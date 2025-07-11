<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Validation\Rules\InEnum;

enum TestStringEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

enum TestIntEnum: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
}

enum TestPureEnum
{
    case FIRST;
    case SECOND;
    case THIRD;
}

enum TestEmptyEnum
{
    // Enum vide pour tester les cas limites
}

describe('InEnum validation rule', function () {
    it('validates string backed enum values correctly', function () {
        $rule = new InEnum(TestStringEnum::class);

        expect($rule->passes('status', 'active'))->toBeTrue();
        expect($rule->passes('status', 'inactive'))->toBeTrue();
        expect($rule->passes('status', 'pending'))->toBeTrue();
        expect($rule->passes('status', 'invalid'))->toBeFalse();
        expect($rule->passes('status', ''))->toBeFalse();
        expect($rule->passes('status', null))->toBeFalse();
    });

    it('validates integer backed enum values correctly', function () {
        $rule = new InEnum(TestIntEnum::class);

        expect($rule->passes('priority', 1))->toBeTrue();
        expect($rule->passes('priority', 2))->toBeTrue();
        expect($rule->passes('priority', 3))->toBeTrue();
        expect($rule->passes('priority', '1'))->toBeFalse(); // Type strict
        expect($rule->passes('priority', 0))->toBeFalse();
        expect($rule->passes('priority', 4))->toBeFalse();
        expect($rule->passes('priority', null))->toBeFalse();
    });

    it('validates pure enum values correctly', function () {
        $rule = new InEnum(TestPureEnum::class);

        expect($rule->passes('type', 'FIRST'))->toBeTrue();
        expect($rule->passes('type', 'SECOND'))->toBeTrue();
        expect($rule->passes('type', 'THIRD'))->toBeTrue();
        expect($rule->passes('type', 'FOURTH'))->toBeFalse();
        expect($rule->passes('type', 'first'))->toBeFalse(); // Case sensitive
        expect($rule->passes('type', ''))->toBeFalse();
        expect($rule->passes('type', null))->toBeFalse();
    });

    it('handles empty enum correctly', function () {
        $rule = new InEnum(TestEmptyEnum::class);

        expect($rule->passes('empty', 'anything'))->toBeFalse();
        expect($rule->passes('empty', ''))->toBeFalse();
        expect($rule->passes('empty', null))->toBeFalse();
    });

    it('handles non-existent enum class', function () {
        $rule = new InEnum('NonExistentEnum');

        expect($rule->passes('field', 'value'))->toBeFalse();
        expect($rule->passes('field', null))->toBeFalse();
    });

    it('handles non-enum class', function () {
        $rule = new InEnum('stdClass');

        expect($rule->passes('field', 'value'))->toBeFalse();
        expect($rule->passes('field', null))->toBeFalse();
    });

    it('handles invalid enum class syntax', function () {
        $rule = new InEnum('');

        expect($rule->passes('field', 'value'))->toBeFalse();
        expect($rule->passes('field', null))->toBeFalse();
    });

    it('returns correct validation message', function () {
        $rule = new InEnum(TestStringEnum::class);

        expect($rule->message())->toBe('The :attribute field must be a valid '.TestStringEnum::class.' value.');
    });

    it('handles edge cases and type coercion', function () {
        $stringRule = new InEnum(TestStringEnum::class);
        $intRule = new InEnum(TestIntEnum::class);

        // Test strict type checking
        expect($stringRule->passes('field', 1))->toBeFalse();
        expect($stringRule->passes('field', true))->toBeFalse();
        expect($stringRule->passes('field', []))->toBeFalse();
        expect($stringRule->passes('field', new stdClass()))->toBeFalse();

        expect($intRule->passes('field', 'active'))->toBeFalse();
        expect($intRule->passes('field', true))->toBeFalse();
        expect($intRule->passes('field', []))->toBeFalse();
        expect($intRule->passes('field', new stdClass()))->toBeFalse();
    });

    it('handles mixed enum types in validation', function () {
        $stringRule = new InEnum(TestStringEnum::class);
        $intRule = new InEnum(TestIntEnum::class);
        $pureRule = new InEnum(TestPureEnum::class);

        // Cross-validation entre types différents
        expect($stringRule->passes('field', 1))->toBeFalse();
        expect($intRule->passes('field', 'active'))->toBeFalse();
        expect($pureRule->passes('field', 'active'))->toBeFalse();
        expect($pureRule->passes('field', 1))->toBeFalse();
    });

    it('handles exceptions and edge cases gracefully', function () {
        // Créer une règle avec une classe d'enum valide
        $rule = new InEnum(TestStringEnum::class);

        // Tester avec des valeurs qui pourraient causer des erreurs
        expect($rule->passes('field', new class {}))->toBeFalse();
        expect($rule->passes('field', []))->toBeFalse();
        expect($rule->passes('field', new stdClass()))->toBeFalse();
        expect($rule->passes('field', function () {}))->toBeFalse();

        // Tester avec des ressources qui pourraient causer des erreurs
        $resource = fopen('php://memory', 'r');
        expect($rule->passes('field', $resource))->toBeFalse();
        fclose($resource);
    });
});
