<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Transformers;

use Illuminate\Support\Str;
use InvalidArgumentException;

final class FieldTransformerRegistry
{
    private array $transformers = [];

    public function __construct()
    {
        $this->registerDefaultTransformers();
    }

    public function transform(mixed $value, array $transformers): mixed
    {
        foreach ($transformers as $transformer) {
            if (is_string($transformer) && str_contains($transformer, ':')) {
                [$name, $params] = explode(':', $transformer, 2);
                $params = explode(',', $params);
                $value = $this->applyTransformer($name, $value, $params);
            } else {
                $value = $this->applyTransformer($transformer, $value);
            }
        }

        return $value;
    }

    public function register(string $name, callable $transformer): void
    {
        $this->transformers[$name] = $transformer;
    }

    private function registerDefaultTransformers(): void
    {
        $this->transformers = [
            'trim' => fn ($value) => is_string($value) ? mb_trim($value) : $value,
            'lowercase' => fn ($value) => is_string($value) ? mb_strtolower($value) : $value,
            'uppercase' => fn ($value) => is_string($value) ? mb_strtoupper($value) : $value,
            'title_case' => fn ($value) => is_string($value) ? Str::title($value) : $value,
            'slugify' => fn ($value) => is_string($value) ? Str::slug($value) : $value,
            'abs' => fn ($value) => is_numeric($value) ? abs($value) : $value,
            'encrypt' => fn ($value) => is_string($value) ? encrypt($value) : $value,
            'normalize_phone' => fn ($value): mixed => $this->normalizePhone($value),
            'clamp_max' => fn ($value, $max) => is_numeric($value) ? min($value, $max) : $value,
            'clamp_min' => fn ($value, $min) => is_numeric($value) ? max($value, $min) : $value,
        ];
    }

    private function applyTransformer(string $name, mixed $value, array $params = []): mixed
    {
        if (! isset($this->transformers[$name])) {
            throw new InvalidArgumentException("Unknown transformer: {$name}");
        }

        $transformer = $this->transformers[$name];

        return count($params) > 0
            ? $transformer($value, ...$params)
            : $transformer($value);
    }

    private function normalizePhone(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        // Remove all non-digit characters except +
        $phone = preg_replace('/[^\d+]/', '', $value);

        // Add +33 for French numbers if they start with 0
        if (str_starts_with($phone, '0')) {
            return '+33'.mb_substr($phone, 1);
        }

        return $phone;
    }
}
