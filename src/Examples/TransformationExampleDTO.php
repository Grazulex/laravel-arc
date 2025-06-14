<?php

namespace Grazulex\Arc\Examples;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Transformers\HashTransformer;
use Grazulex\Arc\Transformers\LowercaseTransformer;
use Grazulex\Arc\Transformers\TrimTransformer;
use Grazulex\Arc\Transformers\UppercaseTransformer;

/**
 * Example DTO demonstrating transformation pipelines.
 */
class TransformationExampleDTO extends LaravelArcDTO
{
    // Email: trim whitespace and convert to lowercase
    #[Property(type: 'string', required: true, transform: [TrimTransformer::class, LowercaseTransformer::class])]
    public string $email;

    // Name: trim whitespace only
    #[Property(type: 'string', required: true, transform: [TrimTransformer::class])]
    public string $name;

    // Country code: trim and convert to uppercase
    #[Property(type: 'string', required: false, transform: [TrimTransformer::class, UppercaseTransformer::class])]
    public ?string $country_code;

    // Password: hash the value (in real scenarios, use Laravel's Hash facade)
    #[Property(type: 'string', required: true, transform: [HashTransformer::class])]
    public string $password_hash;

    // Phone: just trim (no case transformation needed)
    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $phone;

    // Age: no transformations needed for integers
    #[Property(type: 'int', required: true)]
    public int $age;
}
