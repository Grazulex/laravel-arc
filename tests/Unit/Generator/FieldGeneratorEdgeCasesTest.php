<?php

declare(strict_types=1);

use Grazulex\LaravelArc\Generator\DtoGenerationContext;
use Grazulex\LaravelArc\Generator\Fields\DtoFieldGenerator;
use Grazulex\LaravelArc\Generator\Fields\EnumFieldGenerator;

describe('Field Generator Edge Cases', function () {
    beforeEach(function () {
        $this->context = new DtoGenerationContext();
    });

    describe('DtoFieldGenerator', function () {
        beforeEach(function () {
            $this->generator = new DtoFieldGenerator();
        });

        it('supports dto type', function () {
            expect($this->generator->supports('dto'))->toBe(true);
            expect($this->generator->supports('object'))->toBe(false);
            expect($this->generator->supports('string'))->toBe(false);
        });

        it('generates dto field with required nullable', function () {
            $config = [
                'type' => 'dto',
                'dto' => 'UserDto',
                'required' => true,
                'nullable' => true,
            ];

            $result = $this->generator->generate('user', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public readonly ?\\UserDto $user');
        });

        it('generates dto field with default value', function () {
            $config = [
                'type' => 'dto',
                'dto' => 'UserDto',
                'required' => false,
                'default' => 'null',
            ];

            $result = $this->generator->generate('user', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public readonly ?\\UserDto $user = null');
        });

        it('generates dto field with fully qualified class name', function () {
            $config = [
                'type' => 'dto',
                'dto' => 'App\\DTOs\\UserDto',
                'required' => true,
            ];

            $result = $this->generator->generate('user', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public readonly \\App\\DTOs\\UserDto $user');
        });
    });

    describe('EnumFieldGenerator', function () {
        beforeEach(function () {
            $this->generator = new EnumFieldGenerator();
        });

        it('supports enum type', function () {
            expect($this->generator->supports('enum'))->toBe(true);
            expect($this->generator->supports('string'))->toBe(false);
        });

        it('generates enum field with PHP enum class', function () {
            $config = [
                'type' => 'enum',
                'enum_class' => 'UserStatus',
                'required' => true,
            ];

            $result = $this->generator->generate('status', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public readonly \\UserStatus $status');
        });

        it('generates enum field with nullable PHP enum class', function () {
            $config = [
                'type' => 'enum',
                'enum_class' => 'UserStatus',
                'required' => false,
                'nullable' => true,
            ];

            $result = $this->generator->generate('status', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public readonly ?\\UserStatus $status = null');
        });

        it('generates enum field with default value', function () {
            $config = [
                'type' => 'enum',
                'enum_class' => 'UserStatus',
                'required' => false,
                'default' => 'UserStatus::ACTIVE',
            ];

            $result = $this->generator->generate('status', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public readonly ?\\UserStatus $status = UserStatus::ACTIVE');
        });

        it('generates enum field with fully qualified enum class', function () {
            $config = [
                'type' => 'enum',
                'enum_class' => 'App\\Enums\\UserStatus',
                'required' => true,
            ];

            $result = $this->generator->generate('status', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public readonly \\App\\Enums\\UserStatus $status');
        });

        it('fallback to string for array-based enum', function () {
            $config = [
                'type' => 'enum',
                'values' => ['active', 'inactive'],
                'required' => true,
            ];

            $result = $this->generator->generate('status', $config, $this->context);

            expect($result)->toBeString();
            expect($result)->toContain('public string $status');
        });
    });
});
