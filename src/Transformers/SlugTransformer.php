<?php

namespace Grazulex\Arc\Transformers;

use Grazulex\Arc\Interfaces\TransformerInterface;
use Illuminate\Support\Str;

use function strlen;

class SlugTransformer implements TransformerInterface
{
    public function __construct(
        private ?string $sourceField = null,
        private string $separator = '-',
        private ?string $language = null,
        private ?int $maxLength = null,
    ) {}

    /**
     * @param array<string, mixed> $context
     */
    public function transform(mixed $value, array $context = []): mixed
    {
        // Si une source est spécifiée, utiliser cette valeur plutôt que $value
        if ($this->sourceField && isset($context[$this->sourceField])) {
            $sourceValue = $context[$this->sourceField];
        } else {
            $sourceValue = $value;
        }

        // Si pas de valeur source, retourner la valeur originale
        if (empty($sourceValue)) {
            return $value;
        }

        // Générer le slug
        $slug = Str::slug($sourceValue, $this->separator, $this->language);

        // Appliquer une limite de longueur si définie
        if ($this->maxLength && strlen($slug) > $this->maxLength) {
            // Trouver la dernière position du séparateur avant la limite
            $truncated = substr($slug, 0, $this->maxLength);
            $lastSeparatorPos = strrpos($truncated, $this->separator);

            if ($lastSeparatorPos !== false) {
                // Couper au dernier séparateur pour préserver les mots
                $slug = substr($slug, 0, $lastSeparatorPos);
            } else {
                // Si pas de séparateur trouvé, utiliser la troncature simple
                $slug = $truncated;
            }

            // S'assurer qu'on ne termine pas par un séparateur
            $slug = rtrim($slug, $this->separator);
        }

        return $slug;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function shouldTransform(mixed $value, array $context = []): bool
    {
        // Si on a un champ source, vérifier qu'il existe dans le contexte
        if ($this->sourceField) {
            return isset($context[$this->sourceField]) && !empty($context[$this->sourceField]);
        }

        // Sinon, transformer si la valeur n'est pas vide
        return !empty($value);
    }

    /**
     * Factory method pour créer un transformer avec un champ source.
     */
    /**
     * @param array<string, mixed> $options
     */
    public static function from(string $sourceField, array $options = []): self
    {
        return new self(
            sourceField: $sourceField,
            separator: $options['separator'] ?? '-',
            language: $options['language'] ?? null,
            maxLength: $options['maxLength'] ?? null,
        );
    }

    /**
     * Factory method pour un slug simple.
     */
    /**
     * @param array<string, mixed> $options
     */
    public static function make(array $options = []): self
    {
        return new self(
            separator: $options['separator'] ?? '-',
            language: $options['language'] ?? null,
            maxLength: $options['maxLength'] ?? null,
        );
    }
}
