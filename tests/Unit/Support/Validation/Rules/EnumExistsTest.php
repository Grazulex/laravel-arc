<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Validation\Rules\EnumExists;

enum TestStringExistsEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

enum TestIntExistsEnum: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
}

enum TestPureExistsEnum
{
    case FIRST;
    case SECOND;
    case THIRD;
}

enum TestEmptyExistsEnum
{
    // Enum vide pour tester les cas limites
}

describe('EnumExists validation rule', function () {
    it('validates string backed enum values correctly', function () {
        $rule = new EnumExists(TestStringExistsEnum::class);

        expect($rule->passes('status', 'active'))->toBeTrue();
        expect($rule->passes('status', 'inactive'))->toBeTrue();
        expect($rule->passes('status', 'pending'))->toBeTrue();
        expect($rule->passes('status', 'invalid'))->toBeFalse();
        expect($rule->passes('status', ''))->toBeFalse();
        expect($rule->passes('status', null))->toBeFalse();
    });

    it('validates integer backed enum values correctly', function () {
        $rule = new EnumExists(TestIntExistsEnum::class);

        expect($rule->passes('priority', 1))->toBeTrue();
        expect($rule->passes('priority', 2))->toBeTrue();
        expect($rule->passes('priority', 3))->toBeTrue();
        expect($rule->passes('priority', '1'))->toBeFalse(); // Type strict pour tryFrom
        expect($rule->passes('priority', 0))->toBeFalse();
        expect($rule->passes('priority', 4))->toBeFalse();
        expect($rule->passes('priority', null))->toBeFalse();
    });

    it('validates pure enum values correctly', function () {
        $rule = new EnumExists(TestPureExistsEnum::class);

        expect($rule->passes('type', 'FIRST'))->toBeTrue();
        expect($rule->passes('type', 'SECOND'))->toBeTrue();
        expect($rule->passes('type', 'THIRD'))->toBeTrue();
        expect($rule->passes('type', 'FOURTH'))->toBeFalse();
        expect($rule->passes('type', 'first'))->toBeFalse(); // Case sensitive
        expect($rule->passes('type', ''))->toBeFalse();
        expect($rule->passes('type', null))->toBeFalse();
    });

    it('handles empty enum correctly', function () {
        $rule = new EnumExists(TestEmptyExistsEnum::class);

        expect($rule->passes('empty', 'anything'))->toBeFalse();
        expect($rule->passes('empty', ''))->toBeFalse();
        expect($rule->passes('empty', null))->toBeFalse();
    });

    it('handles non-existent enum class', function () {
        $rule = new EnumExists('NonExistentEnum');

        expect($rule->passes('field', 'value'))->toBeFalse();
        expect($rule->passes('field', null))->toBeFalse();
    });

    it('handles non-enum class', function () {
        $rule = new EnumExists('stdClass');

        expect($rule->passes('field', 'value'))->toBeFalse();
        expect($rule->passes('field', null))->toBeFalse();
    });

    it('handles invalid enum class syntax', function () {
        $rule = new EnumExists('');

        expect($rule->passes('field', 'value'))->toBeFalse();
        expect($rule->passes('field', null))->toBeFalse();
    });

    it('returns correct validation message', function () {
        $rule = new EnumExists(TestStringExistsEnum::class);

        expect($rule->message())->toBe('The :attribute field must be a valid case of the '.TestStringExistsEnum::class.' enum.');
    });

    it('handles edge cases and type coercion', function () {
        $stringRule = new EnumExists(TestStringExistsEnum::class);
        $intRule = new EnumExists(TestIntExistsEnum::class);

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
        $stringRule = new EnumExists(TestStringExistsEnum::class);
        $intRule = new EnumExists(TestIntExistsEnum::class);
        $pureRule = new EnumExists(TestPureExistsEnum::class);

        // Cross-validation entre types différents
        expect($stringRule->passes('field', 1))->toBeFalse();
        expect($intRule->passes('field', 'active'))->toBeFalse();
        expect($pureRule->passes('field', 'active'))->toBeFalse();
        expect($pureRule->passes('field', 1))->toBeFalse();
    });

    it('tests pure enum without tryFrom method', function () {
        $rule = new EnumExists(TestPureExistsEnum::class);

        // Vérifier que les pure enums n'ont pas de méthode tryFrom
        expect(method_exists(TestPureExistsEnum::class, 'tryFrom'))->toBeFalse();

        // Mais la validation devrait fonctionner via les noms
        expect($rule->passes('type', 'FIRST'))->toBeTrue();
        expect($rule->passes('type', 'INVALID'))->toBeFalse();
    });

    it('tests backed enum with tryFrom method', function () {
        $rule = new EnumExists(TestStringExistsEnum::class);

        // Vérifier que les backed enums ont une méthode tryFrom
        expect(method_exists(TestStringExistsEnum::class, 'tryFrom'))->toBeTrue();

        // Et que la validation fonctionne avec les valeurs
        expect($rule->passes('status', 'active'))->toBeTrue();
        expect($rule->passes('status', 'invalid'))->toBeFalse();
    });

    it('handles exceptions gracefully', function () {
        // Test avec une classe qui pourrait lever une exception
        $rule = new EnumExists('InvalidEnumClass');

        // Doit retourner false sans lever d'exception
        expect($rule->passes('field', 'value'))->toBeFalse();
        expect($rule->passes('field', null))->toBeFalse();
        expect($rule->passes('field', []))->toBeFalse();
        expect($rule->passes('field', new stdClass()))->toBeFalse();
    });
});
