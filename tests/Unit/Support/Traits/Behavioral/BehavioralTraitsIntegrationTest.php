<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Traits\Behavioral;

use Carbon\Carbon;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasSoftDeletes;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasTimestamps;
use Grazulex\LaravelArc\Support\Traits\Behavioral\HasUuid;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;

// Exemple de DTO utilisant les traits comportementaux
final readonly class TestUserDto
{
    use DtoUtilities;
    use HasSoftDeletes;
    use HasTimestamps;
    use HasUuid;

    public function __construct(
        public string $name,
        public string $email,
        public readonly ?Carbon $deleted_at = null,
        public readonly ?Carbon $created_at = null,
        public readonly ?Carbon $updated_at = null,
        public readonly ?string $id = null, // Changed from uuid to id
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'deleted_at' => $this->deleted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'id' => $this->id,
        ];
    }

    public function with(array $attributes): static
    {
        return new self(
            name: $attributes['name'] ?? $this->name,
            email: $attributes['email'] ?? $this->email,
            deleted_at: array_key_exists('deleted_at', $attributes) ? $attributes['deleted_at'] : $this->deleted_at,
            created_at: array_key_exists('created_at', $attributes) ? $attributes['created_at'] : $this->created_at,
            updated_at: array_key_exists('updated_at', $attributes) ? $attributes['updated_at'] : $this->updated_at,
            id: array_key_exists('id', $attributes) ? $attributes['id'] : $this->id,
        );
    }
}

describe('Behavioral Traits Integration', function () {

    describe('HasSoftDeletes trait', function () {
        it('can mark DTO as deleted', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');

            expect($dto->isDeleted())->toBeFalse()
                ->and($dto->isNotDeleted())->toBeTrue();

            $deletedDto = $dto->delete();

            expect($deletedDto->isDeleted())->toBeTrue()
                ->and($deletedDto->isNotDeleted())->toBeFalse()
                ->and($deletedDto->deleted_at)->toBeInstanceOf(Carbon::class);
        });

        it('can restore deleted DTO', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');
            $deletedDto = $dto->delete();
            $restoredDto = $deletedDto->restore();

            expect($restoredDto->isDeleted())->toBeFalse()
                ->and($restoredDto->deleted_at)->toBeNull();
        });
    });

    describe('HasTimestamps trait', function () {
        it('can set creation timestamps', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');
            $timestampedDto = $dto->setCreatedTimestamp();

            expect($timestampedDto->isCreated())->toBeTrue()
                ->and($timestampedDto->created_at)->toBeInstanceOf(Carbon::class)
                ->and($timestampedDto->updated_at)->toBeInstanceOf(Carbon::class);
        });

        it('can touch updated timestamp', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');
            $createdDto = $dto->setCreatedTimestamp();

            // Simulate some time passing
            sleep(1);

            $touchedDto = $createdDto->touch();

            expect($touchedDto->wasRecentlyUpdated())->toBeTrue()
                ->and($touchedDto->updated_at)->toBeInstanceOf(Carbon::class);
        });

        it('can calculate age in seconds', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');
            $createdDto = $dto->setCreatedTimestamp();

            $age = $createdDto->getAgeInSeconds();

            expect($age)->toBeInt()
                ->and($age)->toBeGreaterThanOrEqual(0);
        });
    });

    describe('HasUuid trait', function () {
        it('can generate UUID', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');

            expect($dto->hasUuid())->toBeFalse();

            $uuidDto = $dto->generateUuid();

            expect($uuidDto->hasUuid())->toBeTrue()
                ->and($uuidDto->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
        });

        it('can set custom UUID', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');
            $customUuid = '550e8400-e29b-41d4-a716-446655440000';

            $uuidDto = $dto->setUuid($customUuid);

            expect($uuidDto->id)->toBe($customUuid);
        });

        it('can get or generate UUID', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');

            $uuid = $dto->getOrGenerateUuid();

            expect($uuid)->toBeString()
                ->and($uuid)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
        });
    });

    describe('Combined traits usage', function () {
        it('can use multiple traits together', function () {
            $dto = new TestUserDto('John Doe', 'john@example.com');

            $enrichedDto = $dto
                ->setCreatedTimestamp()
                ->generateUuid();

            expect($enrichedDto->isCreated())->toBeTrue()
                ->and($enrichedDto->hasUuid())->toBeTrue()
                ->and($enrichedDto->isNotDeleted())->toBeTrue();

            $deletedDto = $enrichedDto->delete();

            expect($deletedDto->isDeleted())->toBeTrue()
                ->and($deletedDto->isCreated())->toBeTrue()
                ->and($deletedDto->hasUuid())->toBeTrue();
        });
    });
});
