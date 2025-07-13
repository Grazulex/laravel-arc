<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Support\Traits\ValidatesData;
use Illuminate\Validation\ValidationException;

// Test DTO class using ValidatesData trait
final class ValidatedTestDto
{
    use ValidatesData;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?int $age = null
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
        ];
    }
}

describe('ValidatesData Trait', function () {
    describe('Data Validation', function () {
        describe('successful validation', function () {
            it('validates data successfully', function () {
                $data = [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'age' => 30,
                ];

                $validated = ValidatedTestDto::validate($data);
                expect($validated)->toEqual($data);
            });

            it('passes validation for valid data', function () {
                $data = [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'age' => 30,
                ];

                expect(ValidatedTestDto::passes($data))->toBe(true);
            });
        });

        describe('failed validation', function () {
            it('throws validation exception for invalid data', function () {
                $data = [
                    'name' => '', // required but empty
                    'email' => 'invalid-email', // invalid email format
                    'age' => -5, // below minimum
                ];

                expect(fn () => ValidatedTestDto::validate($data))
                    ->toThrow(ValidationException::class);
            });

            it('fails validation for invalid data', function () {
                $data = [
                    'name' => '', // required but empty
                    'email' => 'invalid-email', // invalid email format
                ];

                expect(ValidatedTestDto::fails($data))->toBe(true);
                expect(ValidatedTestDto::passes($data))->toBe(false);
            });
        });
    });

    describe('Validator Creation', function () {
        it('creates validator instance', function () {
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ];

            $validator = ValidatedTestDto::validator($data);
            expect($validator)->toBeInstanceOf(Illuminate\Contracts\Validation\Validator::class);
        });
    });

    describe('Nullable Fields', function () {
        it('handles nullable fields correctly', function () {
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                // age is nullable, so it can be omitted
            ];

            expect(ValidatedTestDto::passes($data))->toBe(true);
        });
    });

    describe('Edge Cases and Boundary Values', function () {
        it('validates edge cases', function () {
            $data = [
                'name' => 'J', // minimum length
                'email' => 'a@b.co', // minimum valid email
                'age' => 0, // minimum age
            ];

            expect(ValidatedTestDto::passes($data))->toBe(true);

            $data = [
                'name' => str_repeat('a', 255), // maximum length
                'email' => 'test@example.com',
                'age' => 150, // maximum age
            ];

            expect(ValidatedTestDto::passes($data))->toBe(true);
        });
    });
});
