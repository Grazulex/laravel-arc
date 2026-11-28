<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Validation\DtoDefinitionValidator;
use Illuminate\Support\Facades\File;

describe('DtoDefinitionValidator', function () {
    beforeEach(function () {
        $this->directory = sys_get_temp_dir().'/arc-definition-validator-'.uniqid();
        File::ensureDirectoryExists($this->directory);

        $this->write = function (string $filename, string $content): string {
            $path = $this->directory.'/'.$filename;
            File::put($path, $content);

            return $path;
        };
    });

    afterEach(function () {
        File::deleteDirectory($this->directory);
    });

    it('reports a missing file', function () {
        $result = (new DtoDefinitionValidator())->validateFile($this->directory.'/missing.yaml');

        expect($result['dto'])->toBeNull();
        expect($result['errors'])->toHaveCount(1);
        expect($result['errors'][0])->toContain('File not found');
        expect($result['warnings'])->toBe([]);
    });

    it('rejects a file that is not a YAML mapping', function () {
        $path = ($this->write)('scalar.yaml', "just a string\n");

        $result = (new DtoDefinitionValidator())->validateFile($path);

        expect($result['errors'][0])->toContain('must contain a YAML mapping');
    });

    it('returns the DTO name and warns when no fields are defined', function () {
        $path = ($this->write)('empty.yaml', "header:\n  dto: EmptyDTO\n");

        $result = (new DtoDefinitionValidator())->validateFile($path);

        expect($result['dto'])->toBe('EmptyDTO');
        expect($result['warnings'])->toContain('No fields are defined.');
    });

    it('requires header.dto to be a valid class name', function () {
        $missing = ($this->write)('missing-dto.yaml', "header:\n  table: users\nfields:\n  name:\n    type: string\n");
        $invalid = ($this->write)('invalid-dto.yaml', "header:\n  dto: 'User DTO'\nfields:\n  name:\n    type: string\n");

        $validator = new DtoDefinitionValidator();

        expect($validator->validateFile($missing)['errors'][0])->toContain("Missing required 'header.dto'");
        expect($validator->validateFile($invalid)['errors'][0])->toContain("Invalid DTO class name 'User DTO'");
    });

    it('rejects list-style fields and scalar field definitions', function () {
        $list = ($this->write)('list.yaml', <<<'YAML'
header:
  dto: ListDTO
fields:
  - name: id
    type: integer
YAML);

        $scalar = ($this->write)('scalar-field.yaml', <<<'YAML'
header:
  dto: ScalarDTO
fields:
  name: string
  2fa:
    type: string
YAML);

        $validator = new DtoDefinitionValidator();

        expect($validator->validateFile($list)['errors'][0])->toContain("'fields' must be a mapping");

        $errors = implode("\n", $validator->validateFile($scalar)['errors']);
        expect($errors)->toContain("Field 'name': the definition must be a mapping");
        expect($errors)->toContain("Field '2fa': the name is not a valid PHP property name");
    });

    it('accepts the ModelSchema field types and aliases', function () {
        $path = ($this->write)('types.yaml', <<<'YAML'
header:
  dto: TypesDTO
fields:
  location:
    type: point
  contact:
    type: email
  amount:
    type: money
  views:
    type: bigInteger
  tags:
    type: array
    items:
      type: string
YAML);

        $errors = implode("\n", (new DtoDefinitionValidator())->validateFile($path)['errors']);

        expect($errors)->not->toContain('unknown type');
        expect($errors)->not->toContain('unknown item type');
    });

    it('checks enum classes, array items and field flags', function () {
        $path = ($this->write)('checks.yaml', <<<'YAML'
header:
  dto: ChecksDTO
fields:
  status:
    type: enum
    class: Tests\Fixtures\Enums\Status
  priority:
    type: enum
    class: Tests\Fixtures\Enums\Missing
  kind:
    type: enum
  tags:
    type: array
    items:
      type: strng
  title:
    type: string
    nullable: 'yes'
    length: long
YAML);

        $result = (new DtoDefinitionValidator())->validateFile($path);
        $errors = implode("\n", $result['errors']);

        expect($errors)->not->toContain("Field 'status'");
        expect($errors)->toContain("Field 'priority': enum class 'Tests\\Fixtures\\Enums\\Missing' does not exist");
        expect($errors)->toContain("Field 'tags': unknown item type 'strng'");
        expect($errors)->toContain("Field 'title': 'nullable' must be true or false, got 'yes'");
        expect($errors)->toContain("Field 'title': 'length' must be an integer, got 'long'");
        expect(implode("\n", $result['warnings']))->toContain("Field 'kind': the enum declares neither 'values' nor a 'class'");
    });

    it('resolves nested DTOs from the known DTO names', function () {
        $path = ($this->write)('post.yaml', <<<'YAML'
header:
  dto: PostDTO
fields:
  author:
    type: dto
    dto: App\DTO\UserDTO
  category:
    type: dto
    dto: CategoryDTO
  untyped:
    type: dto
YAML);

        $errors = implode("\n", (new DtoDefinitionValidator())->validateFile($path, ['UserDTO'])['errors']);

        expect($errors)->not->toContain("Field 'author'");
        expect($errors)->toContain("Field 'category': DTO 'CategoryDTO' has no YAML definition");
        expect($errors)->toContain("Field 'untyped': the nested DTO must be declared with the 'dto' key");
    });

    it('accepts existing classes as relation targets', function () {
        $path = ($this->write)('relations.yaml', <<<'YAML'
header:
  dto: RelationsDTO
fields:
  name:
    type: string
relations:
  validator:
    type: has_one
    model: Grazulex\LaravelArc\Support\Validation\DtoDefinitionValidator
  commentable:
    type: morphTo
  tags:
    - broken
YAML);

        $errors = implode("\n", (new DtoDefinitionValidator())->validateFile($path)['errors']);

        expect($errors)->not->toContain("Relation 'validator'");
        expect($errors)->not->toContain("Relation 'commentable'");
        expect($errors)->toContain("Relation 'tags': the type is required");
    });

    it('accepts rule classes and rejects missing ones', function () {
        $path = ($this->write)('rules.yaml', <<<'YAML'
header:
  dto: RulesDTO
fields:
  status:
    type: string
    rules:
      - sometimes
      - 'in:draft,published'
      - 'regex:/^[a-z,]+$/'
      - Grazulex\LaravelArc\Support\Validation\Rules\InEnum
      - App\Rules\Missing
      - 42
YAML);

        $errors = (new DtoDefinitionValidator())->validateFile($path)['errors'];

        expect($errors)->toHaveCount(2);
        expect($errors[0])->toContain("validation rule class 'App\\Rules\\Missing' does not exist");
        expect($errors[1])->toContain("each entry of 'rules' must be a non-empty string, got 42");
    });

    it('collects the DTO names declared by definition files', function () {
        $user = ($this->write)('user.yaml', "header:\n  dto: UserDTO\n");
        $admin = ($this->write)('admin.yaml', "header:\n  class: App\\DTO\\AdminDTO\n");
        $broken = ($this->write)('broken.yaml', "header:\n  dto: [unclosed\n");
        $legacy = ($this->write)('legacy.yaml', "dto: LegacyDTO\n");

        expect(DtoDefinitionValidator::declaredDtoNames([$user, $admin, $broken, $legacy]))
            ->toBe(['UserDTO', 'AdminDTO']);
    });
});
