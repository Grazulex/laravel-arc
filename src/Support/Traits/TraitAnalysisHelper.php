<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc\Support\Traits;

/**
 * This class exists only to help PHPStan analyze the traits.
 * It's not meant to be used in production code.
 *
 * @internal
 */
final class TraitAnalysisHelper
{
    use ConvertsData;
    use DtoUtilities;
    use ValidatesData;

    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
        );
    }

    public static function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'name' => ['required', 'string'],
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
