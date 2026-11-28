<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Validation;

use Grazulex\LaravelArc\Generator\DtoGenerator;
use Grazulex\LaravelArc\Services\AdvancedModelSchemaIntegrationService;
use Grazulex\LaravelArc\Services\ModelSchemaFieldTypeMapper;
use Grazulex\LaravelArc\Support\DtoNamespaceResolver;
use Grazulex\LaravelArc\Support\DtoPathResolver;
use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;
use Grazulex\LaravelModelschema\Support\FieldTypeRegistry;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as LaravelValidator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * Validates a YAML DTO definition without generating anything.
 *
 * The definition is first checked statically (header, traits, field types, validation
 * rules, nested DTOs and relations), then rendered in memory through the DTO generator
 * so that anything that would fail at generation time is reported as well.
 */
final class DtoDefinitionValidator
{
    /**
     * Field types handled by Arc itself, on top of the ModelSchema ones.
     *
     * @var list<string>
     */
    private const ARC_FIELD_TYPES = [
        'string', 'text', 'integer', 'int', 'float', 'double', 'decimal', 'number',
        'boolean', 'bool', 'array', 'json', 'object', 'datetime', 'date', 'time',
        'timestamp', 'uuid', 'enum', 'id', 'dto', 'ipAddress', 'macAddress',
    ];

    /**
     * @var list<string>
     */
    private const RELATION_TYPES = [
        'hasOne', 'hasMany', 'belongsTo', 'belongsToMany',
        'morphTo', 'morphOne', 'morphMany', 'morphToMany',
    ];

    /**
     * Minimum number of parameters required by the built-in Laravel rules.
     *
     * @var array<string, int>
     */
    private const RULE_MIN_PARAMETERS = [
        'after' => 1,
        'after_or_equal' => 1,
        'before' => 1,
        'before_or_equal' => 1,
        'between' => 2,
        'date_equals' => 1,
        'date_format' => 1,
        'decimal' => 1,
        'different' => 1,
        'digits' => 1,
        'digits_between' => 2,
        'ends_with' => 1,
        'exists' => 1,
        'gt' => 1,
        'gte' => 1,
        'in' => 1,
        'in_array' => 1,
        'lt' => 1,
        'lte' => 1,
        'max' => 1,
        'max_digits' => 1,
        'mimes' => 1,
        'mimetypes' => 1,
        'min' => 1,
        'min_digits' => 1,
        'multiple_of' => 1,
        'not_in' => 1,
        'not_regex' => 1,
        'regex' => 1,
        'required_if' => 2,
        'required_unless' => 2,
        'required_with' => 1,
        'required_with_all' => 1,
        'required_without' => 1,
        'required_without_all' => 1,
        'same' => 1,
        'size' => 1,
        'starts_with' => 1,
        'unique' => 1,
    ];

    /**
     * Rules whose parameters must all be numeric.
     *
     * @var list<string>
     */
    private const NUMERIC_PARAMETER_RULES = [
        'between', 'digits', 'digits_between', 'max', 'max_digits', 'min', 'min_digits', 'multiple_of', 'size',
    ];

    /**
     * Public "validate*" methods of the Laravel validator that are not validation rules.
     *
     * @var list<string>
     */
    private const NOT_RULES = ['d', 'attribute', 'with_bag', 'using_custom_rule'];

    private const IDENTIFIER_PATTERN = '/^[A-Za-z_]\w*$/';

    /** @var list<string> */
    private array $errors = [];

    /** @var list<string> */
    private array $warnings = [];

    /** @var list<string> */
    private array $knownDtos = [];

    /** @var array<string, string>|null */
    private ?array $fieldTypes = null;

    /** @var array<string, mixed>|null */
    private ?array $ruleExtensions = null;

    /**
     * Collect the DTO class short names declared by a list of YAML definition files.
     * Files that cannot be parsed are ignored.
     *
     * @param  iterable<string>  $files
     * @return list<string>
     */
    public static function declaredDtoNames(iterable $files): array
    {
        $names = [];

        foreach ($files as $file) {
            try {
                $definition = Yaml::parseFile($file);
            } catch (Throwable) {
                continue;
            }

            $name = is_array($definition) && is_array($definition['header'] ?? null)
                ? ($definition['header']['dto'] ?? $definition['header']['class'] ?? null)
                : null;

            if (is_string($name) && $name !== '') {
                $names[] = self::shortName($name);
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Validate a YAML definition file.
     *
     * @param  list<string>  $knownDtos  DTO class short names declared by the other definitions,
     *                                   used to resolve nested DTOs and relations
     * @return array{dto: string|null, errors: list<string>, warnings: list<string>}
     */
    public function validateFile(string $filePath, array $knownDtos = []): array
    {
        $this->errors = [];
        $this->warnings = [];
        $this->knownDtos = $knownDtos;

        if (! is_file($filePath)) {
            $this->errors[] = "File not found: {$filePath}";

            return $this->result(null);
        }

        try {
            $definition = Yaml::parseFile($filePath);
        } catch (ParseException $e) {
            $this->errors[] = $this->describeParseError($e);

            return $this->result(null);
        }

        if (! is_array($definition)) {
            $this->errors[] = "The file must contain a YAML mapping with a 'header' and a 'fields' section.";

            return $this->result(null);
        }

        $dtoName = $this->validateHeader($definition);
        $this->validateFields($definition);
        $this->validateRelations($definition);

        // Only render the DTO when the static checks pass, to avoid reporting the same problem twice
        if ($this->errors === []) {
            $this->validateGeneration($filePath);
        }

        return $this->result($dtoName);
    }

    private static function shortName(string $class): string
    {
        $class = mb_trim($class, '\\');
        $position = mb_strrpos($class, '\\');

        return $position === false ? $class : mb_substr($class, $position + 1);
    }

    /**
     * @return array{dto: string|null, errors: list<string>, warnings: list<string>}
     */
    private function result(?string $dtoName): array
    {
        return [
            'dto' => $dtoName,
            'errors' => array_values(array_unique($this->errors)),
            'warnings' => array_values(array_unique($this->warnings)),
        ];
    }

    private function describeParseError(ParseException $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Multiple documents are not supported')) {
            return 'Multiple YAML documents detected. Each DTO definition must be in a separate file.';
        }

        if (str_contains($message, 'Complex mappings are not supported')) {
            return 'Complex YAML mapping detected. Use simple key-value pairs.';
        }

        return "Invalid YAML syntax: {$message}";
    }

    /**
     * @param  array<mixed>  $definition
     */
    private function validateHeader(array $definition): ?string
    {
        $header = $definition['header'] ?? null;

        if (! is_array($header)) {
            $hint = isset($definition['dto']) ? " Move the top-level 'dto' key under 'header'." : '';
            $this->errors[] = "Missing 'header' section: 'header.dto' (the DTO class name) is required.{$hint}";

            return null;
        }

        $dtoName = $header['dto'] ?? null;

        if (! is_string($dtoName) || mb_trim($dtoName) === '') {
            $this->errors[] = "Missing required 'header.dto' (the DTO class name).";
            $dtoName = null;
        } elseif (preg_match(self::IDENTIFIER_PATTERN, $dtoName) !== 1) {
            $this->errors[] = "Invalid DTO class name '{$dtoName}' in 'header.dto'.";
        }

        $options = is_array($definition['options'] ?? null) ? $definition['options'] : [];
        $namespace = $header['namespace'] ?? $options['namespace'] ?? null;

        if ($namespace !== null && (! is_string($namespace) || ! DtoPathResolver::isValidNamespace($namespace))) {
            $this->errors[] = 'Invalid namespace '.$this->describe($namespace).'.';
        }

        $model = $header['model'] ?? null;

        if ($model !== null) {
            if (! is_string($model) || $model === '') {
                $this->errors[] = "'header.model' must be a model class name.";
            } elseif (! $this->classExists($this->resolveModelClass($model))) {
                $this->warnings[] = "Header model '{$model}' does not exist.";
            }
        }

        $this->validateTraits($definition['traits'] ?? $header['traits'] ?? null);

        return $dtoName;
    }

    private function validateTraits(mixed $traits): void
    {
        if ($traits === null || $traits === []) {
            return;
        }

        if (! is_array($traits)) {
            $this->errors[] = "'traits' must be a list of behavioral trait names.";

            return;
        }

        $available = BehavioralTraitRegistry::getAvailableTraits();

        foreach ($traits as $trait) {
            if (is_string($trait) && BehavioralTraitRegistry::hasTrail($trait)) {
                continue;
            }

            $suggestion = is_string($trait) ? $this->suggest($trait, $available) : '';
            $this->errors[] = 'Unknown behavioral trait '.$this->describe($trait).".{$suggestion}"
                .' Available traits: '.implode(', ', $available).'.';
        }
    }

    /**
     * @param  array<mixed>  $definition
     */
    private function validateFields(array $definition): void
    {
        $fields = $definition['fields'] ?? null;

        if ($fields === null || $fields === []) {
            $this->warnings[] = 'No fields are defined.';

            return;
        }

        if (! is_array($fields) || array_is_list($fields)) {
            $this->errors[] = "'fields' must be a mapping of field names to definitions (e.g. name: { type: string }), not a list.";

            return;
        }

        foreach ($fields as $name => $config) {
            $name = (string) $name;

            if (preg_match(self::IDENTIFIER_PATTERN, $name) !== 1) {
                $this->errors[] = "Field '{$name}': the name is not a valid PHP property name.";
            }

            if (! is_array($config)) {
                $this->errors[] = "Field '{$name}': the definition must be a mapping (e.g. { type: string }).";

                continue;
            }

            $this->validateField($name, $config);
        }
    }

    /**
     * @param  array<mixed>  $config
     */
    private function validateField(string $name, array $config): void
    {
        $label = "Field '{$name}'";
        $type = $config['type'] ?? 'string';

        if (! is_string($type) || $type === '') {
            $this->errors[] = "{$label}: the type must be a non-empty string.";
        } elseif (! $this->isKnownFieldType($type)) {
            $this->errors[] = "{$label}: unknown type '{$type}'.".$this->suggest($type, array_values($this->fieldTypes()));
        } else {
            $normalized = $this->normalizeType($type);

            if ($normalized === 'dto') {
                $this->validateDtoReference($label, $config['dto'] ?? null);
            } elseif ($normalized === 'enum') {
                $this->validateEnum($label, $config);
            } elseif (in_array($normalized, ['array', 'set', 'collection'], true)) {
                $this->validateItems($label, $config['items'] ?? null);
            }
        }

        foreach (['required', 'nullable', 'unique', 'index'] as $key) {
            if (isset($config[$key]) && ! is_bool($config[$key])) {
                $this->errors[] = "{$label}: '{$key}' must be true or false, got ".$this->describe($config[$key]).'.';
            }
        }

        foreach (['length', 'precision', 'scale'] as $key) {
            if (isset($config[$key]) && ! is_int($config[$key])) {
                $this->errors[] = "{$label}: '{$key}' must be an integer, got ".$this->describe($config[$key]).'.';
            }
        }

        $this->validateRules($label, 'rules', $config['rules'] ?? null);
        $this->validateRules($label, 'validation', $config['validation'] ?? null);
    }

    /**
     * @param  array<mixed>  $config
     */
    private function validateEnum(string $label, array $config): void
    {
        $class = $config['enum_class'] ?? $config['class'] ?? null;

        if ($class !== null) {
            if (! is_string($class) || ! enum_exists(mb_ltrim($class, '\\'))) {
                $this->errors[] = "{$label}: enum class ".$this->describe($class).' does not exist.';
            }

            return;
        }

        $values = $config['values'] ?? null;

        if (! is_array($values) || $values === []) {
            $this->warnings[] = "{$label}: the enum declares neither 'values' nor a 'class'.";
        }
    }

    private function validateItems(string $label, mixed $items): void
    {
        if (! is_array($items) || ! isset($items['type'])) {
            return;
        }

        $type = $items['type'];

        if (! is_string($type) || ! $this->isKnownFieldType($type)) {
            $this->errors[] = "{$label}: unknown item type ".$this->describe($type).'.'
                .(is_string($type) ? $this->suggest($type, array_values($this->fieldTypes())) : '');

            return;
        }

        if ($this->normalizeType($type) === 'dto') {
            $this->validateDtoReference("{$label} (items)", $items['dto'] ?? $items['class'] ?? null);
        }
    }

    private function validateDtoReference(string $label, mixed $dto): void
    {
        if (! is_string($dto) || $dto === '') {
            $this->errors[] = "{$label}: the nested DTO must be declared with the 'dto' key.";

            return;
        }

        if (in_array(self::shortName($dto), $this->knownDtos, true)) {
            return;
        }

        if ($this->classExists($dto) || $this->classExists(DtoNamespaceResolver::resolveDtoClass($dto))) {
            return;
        }

        $this->errors[] = "{$label}: DTO '{$dto}' has no YAML definition and is not an existing class.";
    }

    private function validateRules(string $label, string $key, mixed $rules): void
    {
        if ($rules === null || $rules === []) {
            return;
        }

        if (! is_array($rules)) {
            $this->errors[] = "{$label}: '{$key}' must be a list of validation rules (e.g. [min:3, max:255]).";

            return;
        }

        foreach ($rules as $rule) {
            if (! is_string($rule) || mb_trim($rule) === '') {
                $this->errors[] = "{$label}: each entry of '{$key}' must be a non-empty string, got ".$this->describe($rule).'.';

                continue;
            }

            $this->validateRule($label, $rule);
        }
    }

    private function validateRule(string $label, string $rule): void
    {
        $parts = explode(':', $rule, 2);
        $name = mb_trim($parts[0]);
        $parameterString = $parts[1] ?? null;

        // A rule given as a class name (custom rule object)
        if (str_contains($name, '\\')) {
            if (! $this->classExists($name)) {
                $this->errors[] = "{$label}: validation rule class '{$name}' does not exist.";
            }

            return;
        }

        $key = Str::snake(Str::studly($name));

        if (! $this->isKnownRule($name)) {
            $this->errors[] = "{$label}: unknown validation rule '{$name}'.".$this->suggest($key, $this->knownRuleNames());

            return;
        }

        $parameters = $this->ruleParameters($key, $parameterString);
        $minimum = self::RULE_MIN_PARAMETERS[$key] ?? 0;

        if (count($parameters) < $minimum) {
            $this->errors[] = "{$label}: validation rule '{$name}' requires at least {$minimum} parameter(s) (got '{$rule}').";

            return;
        }

        if (in_array($key, self::NUMERIC_PARAMETER_RULES, true)) {
            foreach ($parameters as $parameter) {
                if (! is_numeric($parameter)) {
                    $this->errors[] = "{$label}: validation rule '{$rule}' expects numeric parameters, got '{$parameter}'.";

                    return;
                }
            }
        }

        if (in_array($key, ['regex', 'not_regex'], true) && @preg_match($parameters[0], '') === false) {
            $this->errors[] = "{$label}: validation rule '{$rule}' is not a valid regular expression.";
        }
    }

    /**
     * @return list<string>
     */
    private function ruleParameters(string $key, ?string $parameterString): array
    {
        if ($parameterString === null || mb_trim($parameterString) === '') {
            return [];
        }

        // Regular expressions may contain commas and must not be split
        if (in_array($key, ['regex', 'not_regex'], true)) {
            return [$parameterString];
        }

        $parameters = [];
        foreach (str_getcsv($parameterString, ',', '"', '\\') as $parameter) {
            if ($parameter !== null && mb_trim($parameter) !== '') {
                $parameters[] = mb_trim($parameter);
            }
        }

        return $parameters;
    }

    private function isKnownRule(string $name): bool
    {
        if ($name === '') {
            return false;
        }

        $studly = Str::studly($name);
        $key = Str::snake($studly);

        if (in_array($key, self::NOT_RULES, true) || preg_match('/^\w+$/', $studly) !== 1) {
            return false;
        }

        return method_exists(LaravelValidator::class, 'validate'.$studly)
            || array_key_exists($key, $this->ruleExtensions());
    }

    /**
     * @return list<string>
     */
    private function knownRuleNames(): array
    {
        $names = [];

        foreach (get_class_methods(LaravelValidator::class) as $method) {
            if (! str_starts_with($method, 'validate') || mb_strlen($method) <= 8) {
                continue;
            }

            $name = Str::snake(mb_substr($method, 8));

            if (! in_array($name, self::NOT_RULES, true)) {
                $names[] = $name;
            }
        }

        return array_values(array_unique(array_merge($names, array_keys($this->ruleExtensions()))));
    }

    /**
     * Custom rules registered on the validator factory (in_enum, enum_exists, application rules...).
     *
     * @return array<string, mixed>
     */
    private function ruleExtensions(): array
    {
        if ($this->ruleExtensions !== null) {
            return $this->ruleExtensions;
        }

        $extensions = [];

        try {
            foreach (array_keys(ValidatorFacade::make([], [])->extensions) as $extension) {
                $extensions[Str::snake((string) $extension)] = true;
            }
        } catch (Throwable) {
            // No validator factory available: only the built-in rules are known
        }

        return $this->ruleExtensions = $extensions;
    }

    /**
     * @param  array<mixed>  $definition
     */
    private function validateRelations(array $definition): void
    {
        $relations = $definition['relations'] ?? $definition['relationships'] ?? null;

        if ($relations === null || $relations === []) {
            return;
        }

        if (! is_array($relations) || array_is_list($relations)) {
            $this->errors[] = "'relations' must be a mapping of relation names to definitions (e.g. author: { type: belongsTo, dto: UserDTO }), not a list.";

            return;
        }

        foreach ($relations as $name => $config) {
            $name = (string) $name;
            $label = "Relation '{$name}'";

            if (preg_match(self::IDENTIFIER_PATTERN, $name) !== 1) {
                $this->errors[] = "{$label}: the name is not a valid PHP property name.";
            }

            if (! is_array($config)) {
                $this->errors[] = "{$label}: the definition must be a mapping (e.g. { type: belongsTo, dto: UserDTO }).";

                continue;
            }

            $this->validateRelation($label, $config);
        }
    }

    /**
     * @param  array<mixed>  $config
     */
    private function validateRelation(string $label, array $config): void
    {
        $type = $config['type'] ?? null;
        $normalized = is_string($type) ? $this->normalizeType($type) : '';
        $supported = array_map(fn (string $relation): string => $this->normalizeType($relation), self::RELATION_TYPES);

        if (! is_string($type) || $type === '') {
            $this->errors[] = "{$label}: the type is required (".implode(', ', self::RELATION_TYPES).').';
        } elseif (! in_array($normalized, $supported, true)) {
            $this->errors[] = "{$label}: unknown relation type '{$type}'.".$this->suggest($type, self::RELATION_TYPES)
                .' Supported types: '.implode(', ', self::RELATION_TYPES).'.';
        }

        $model = $config['model'] ?? $config['target'] ?? null;
        $dto = $config['dto'] ?? $config['class'] ?? null;

        if ($model === null && $dto === null) {
            if ($normalized !== 'morphto') {
                $this->errors[] = "{$label}: no target declared (use 'dto' for the related DTO or 'model' for the related model).";
            }

            return;
        }

        if ($model !== null) {
            if (! is_string($model) || $model === '') {
                $this->errors[] = "{$label}: the related model must be a class name.";
            } elseif (! $this->classExists($this->resolveModelClass($model))) {
                $this->errors[] = "{$label}: model '".$this->resolveModelClass($model)."' does not exist.";
            }
        }

        if ($dto !== null) {
            $this->validateDtoReference($label, $dto);
        }
    }

    /**
     * Render the DTO in memory, exactly like dto:generate does, and report any failure.
     */
    private function validateGeneration(string $filePath): void
    {
        try {
            $definition = (new AdvancedModelSchemaIntegrationService())->processYamlFile($filePath);

            DtoGenerator::make()->generateFromDefinition($definition, $filePath);
        } catch (Throwable $e) {
            $this->errors[] = 'Generation would fail: '.$e->getMessage();
        }
    }

    private function isKnownFieldType(string $type): bool
    {
        return isset($this->fieldTypes()[$this->normalizeType($type)]);
    }

    private function normalizeType(string $type): string
    {
        return mb_strtolower(str_replace('_', '', mb_trim($type)));
    }

    /**
     * Supported field types, keyed by their normalized name.
     *
     * @return array<string, string>
     */
    private function fieldTypes(): array
    {
        if ($this->fieldTypes !== null) {
            return $this->fieldTypes;
        }

        $types = array_merge(
            self::ARC_FIELD_TYPES,
            array_keys((new ModelSchemaFieldTypeMapper())->getAllMappings()),
        );

        try {
            $types = array_merge($types, FieldTypeRegistry::all());
        } catch (Throwable) {
            // ModelSchema registry unavailable: keep the types known by Arc
        }

        $known = [];
        foreach ($types as $type) {
            $type = (string) $type;
            $known[$this->normalizeType($type)] ??= $type;
        }

        return $this->fieldTypes = $known;
    }

    private function resolveModelClass(string $model): string
    {
        $model = mb_ltrim(str_replace('\\\\', '\\', $model), '\\');

        return str_contains($model, '\\') ? $model : 'App\\Models\\'.$model;
    }

    private function classExists(string $class): bool
    {
        $class = mb_ltrim($class, '\\');

        if ($class === '') {
            return false;
        }

        try {
            return class_exists($class) || interface_exists($class) || enum_exists($class);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Build a " Did you mean 'x'?" hint from the closest candidate, or an empty string.
     *
     * @param  array<int, string>  $candidates
     */
    private function suggest(string $value, array $candidates): string
    {
        $value = mb_strtolower($value);
        $best = null;
        $bestDistance = 3;

        foreach ($candidates as $candidate) {
            $distance = levenshtein($value, mb_strtolower($candidate));

            if ($distance < $bestDistance) {
                $best = $candidate;
                $bestDistance = $distance;
            }
        }

        return $best === null ? '' : " Did you mean '{$best}'?";
    }

    private function describe(mixed $value): string
    {
        if (is_string($value)) {
            return "'{$value}'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return get_debug_type($value);
    }
}
