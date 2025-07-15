<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Transformers;

use Grazulex\LaravelArc\Support\Transformers\FieldTransformerRegistry;
use InvalidArgumentException;
use Tests\TestCase;

final class FieldTransformerRegistryTest extends TestCase
{
    private FieldTransformerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new FieldTransformerRegistry();
    }

    public function test_it_applies_trim_transformer()
    {
        $result = $this->registry->transform('  hello world  ', ['trim']);
        $this->assertEquals('hello world', $result);
    }

    public function test_it_applies_lowercase_transformer()
    {
        $result = $this->registry->transform('HELLO WORLD', ['lowercase']);
        $this->assertEquals('hello world', $result);
    }

    public function test_it_applies_uppercase_transformer()
    {
        $result = $this->registry->transform('hello world', ['uppercase']);
        $this->assertEquals('HELLO WORLD', $result);
    }

    public function test_it_applies_title_case_transformer()
    {
        $result = $this->registry->transform('hello world', ['title_case']);
        $this->assertEquals('Hello World', $result);
    }

    public function test_it_applies_slugify_transformer()
    {
        $result = $this->registry->transform('Hello World!', ['slugify']);
        $this->assertEquals('hello-world', $result);
    }

    public function test_it_applies_abs_transformer()
    {
        $result = $this->registry->transform(-42, ['abs']);
        $this->assertEquals(42, $result);
    }

    public function test_it_applies_normalize_phone_transformer()
    {
        $result = $this->registry->transform('01.23.45.67.89', ['normalize_phone']);
        $this->assertEquals('+33123456789', $result);
    }

    public function test_it_applies_clamp_max_transformer()
    {
        $result = $this->registry->transform(150, ['clamp_max:100']);
        $this->assertEquals(100, $result);
    }

    public function test_it_applies_clamp_min_transformer()
    {
        $result = $this->registry->transform(5, ['clamp_min:10']);
        $this->assertEquals(10, $result);
    }

    public function test_it_applies_multiple_transformers()
    {
        $result = $this->registry->transform('  HELLO WORLD  ', ['trim', 'lowercase', 'title_case']);
        $this->assertEquals('Hello World', $result);
    }

    public function test_it_applies_transformers_with_parameters()
    {
        $result = $this->registry->transform(150, ['clamp_max:100', 'clamp_min:50']);
        $this->assertEquals(100, $result);
    }

    public function test_it_applies_transformers_with_multiple_parameters()
    {
        $this->registry->register('test_multi', function ($value, $param1, $param2) {
            return $value.'-'.$param1.'-'.$param2;
        });

        $result = $this->registry->transform('hello', ['test_multi:world,test']);
        $this->assertEquals('hello-world-test', $result);
    }

    public function test_it_registers_custom_transformer()
    {
        $this->registry->register('reverse', fn ($value) => is_string($value) ? strrev($value) : $value);

        $result = $this->registry->transform('hello', ['reverse']);
        $this->assertEquals('olleh', $result);
    }

    public function test_it_throws_exception_for_unknown_transformer()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown transformer: unknown');

        $this->registry->transform('test', ['unknown']);
    }

    public function test_it_preserves_non_numeric_values_for_numeric_transformers()
    {
        $result = $this->registry->transform('hello', ['abs']);
        $this->assertEquals('hello', $result);

        $result = $this->registry->transform('hello', ['clamp_max:100']);
        $this->assertEquals('hello', $result);

        $result = $this->registry->transform('hello', ['clamp_min:10']);
        $this->assertEquals('hello', $result);
    }

    public function test_it_preserves_non_string_values_for_string_transformers()
    {
        $result = $this->registry->transform(123, ['trim']);
        $this->assertEquals(123, $result);

        $result = $this->registry->transform(123, ['lowercase']);
        $this->assertEquals(123, $result);

        $result = $this->registry->transform(123, ['uppercase']);
        $this->assertEquals(123, $result);

        $result = $this->registry->transform(123, ['title_case']);
        $this->assertEquals(123, $result);

        $result = $this->registry->transform(123, ['slugify']);
        $this->assertEquals(123, $result);
    }

    public function test_it_normalizes_phone_numbers()
    {
        // French number starting with 0
        $result = $this->registry->transform('01 23 45 67 89', ['normalize_phone']);
        $this->assertEquals('+33123456789', $result);

        // Already formatted international number
        $result = $this->registry->transform('+33123456789', ['normalize_phone']);
        $this->assertEquals('+33123456789', $result);

        // Number with various separators
        $result = $this->registry->transform('01-23-45-67-89', ['normalize_phone']);
        $this->assertEquals('+33123456789', $result);

        // Number with dots and spaces
        $result = $this->registry->transform('01.23.45.67.89', ['normalize_phone']);
        $this->assertEquals('+33123456789', $result);
    }

    public function test_it_handles_non_string_values_for_phone_normalization()
    {
        $result = $this->registry->transform(123, ['normalize_phone']);
        $this->assertEquals(123, $result);

        $result = $this->registry->transform(null, ['normalize_phone']);
        $this->assertEquals(null, $result);
    }

    public function test_it_handles_clamp_with_numeric_values()
    {
        $result = $this->registry->transform(50, ['clamp_max:100']);
        $this->assertEquals(50, $result);

        $result = $this->registry->transform(50, ['clamp_min:10']);
        $this->assertEquals(50, $result);
    }

    public function test_it_handles_float_values()
    {
        $result = $this->registry->transform(-42.5, ['abs']);
        $this->assertEquals(42.5, $result);

        $result = $this->registry->transform(150.5, ['clamp_max:100']);
        $this->assertEquals(100, $result);

        $result = $this->registry->transform(5.5, ['clamp_min:10']);
        $this->assertEquals(10, $result);
    }

    public function test_it_handles_empty_transformer_list()
    {
        $result = $this->registry->transform('hello', []);
        $this->assertEquals('hello', $result);
    }

    public function test_it_handles_transformer_with_colon_but_no_params()
    {
        $this->registry->register('test_colon', fn ($value) => $value.'-test');

        $result = $this->registry->transform('hello', ['test_colon:']);
        $this->assertEquals('hello-test', $result);
    }

    public function test_it_handles_unicode_strings()
    {
        $result = $this->registry->transform('  héllo wörld  ', ['trim']);
        $this->assertEquals('héllo wörld', $result);

        $result = $this->registry->transform('HÉLLO WÖRLD', ['lowercase']);
        $this->assertEquals('héllo wörld', $result);

        $result = $this->registry->transform('héllo wörld', ['uppercase']);
        $this->assertEquals('HÉLLO WÖRLD', $result);
    }

    public function test_it_chains_transformers_correctly()
    {
        $result = $this->registry->transform(-150, ['abs', 'clamp_max:100']);
        $this->assertEquals(100, $result);
    }

    public function test_it_applies_custom_transformer_with_parameters()
    {
        $this->registry->register('prefix', fn ($value, $prefix) => $prefix.$value);

        $result = $this->registry->transform('world', ['prefix:hello-']);
        $this->assertEquals('hello-world', $result);
    }
}
