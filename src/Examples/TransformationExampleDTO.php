<?php

namespace Grazulex\Arc\Examples;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Interfaces\TransformerInterface;
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Transformers\HashTransformer;
use Grazulex\Arc\Transformers\LowercaseTransformer;
use Grazulex\Arc\Transformers\SlugTransformer;
use Grazulex\Arc\Transformers\TrimTransformer;
use Grazulex\Arc\Transformers\UppercaseTransformer;

/**
 * Example transformer for creating slugs from titles.
 */
class TitleToSlugTransformer extends SlugTransformer
{
    public function __construct()
    {
        parent::__construct(
            sourceField: 'title',
            separator: '-',
            maxLength: 50,
        );
    }
}

/**
 * Example transformer for creating username from first and last name.
 */
class UsernameTransformer extends SlugTransformer
{
    public function __construct()
    {
        parent::__construct(
            sourceField: 'full_name',
            separator: '_',
            maxLength: 20,
        );
    }
}

/**
 * Custom transformer that generates full name from first and last name.
 */
class FullNameTransformer implements TransformerInterface
{
    public function transform(mixed $value, array $context = []): mixed
    {
        $firstName = trim($context['first_name'] ?? '');
        $lastName = trim($context['last_name'] ?? '');

        if (empty($firstName) && empty($lastName)) {
            return $value; // Return original if no names available
        }

        return trim($firstName . ' ' . $lastName);
    }

    public function shouldTransform(mixed $value, array $context = []): bool
    {
        return !empty($context['first_name']) || !empty($context['last_name']);
    }
}

/**
 * Example DTO demonstrating advanced transformation pipelines with cross-field transformations.
 *
 * This example showcases:
 * - Basic transformations (trim, case conversion)
 * - Cross-field transformations (slug from title, username from full name)
 * - Context-aware transformations (full name from first/last name)
 * - Chained transformations with multiple steps
 */
class TransformationExampleDTO extends LaravelArcDTO
{
    // === BASIC TRANSFORMATIONS (NO DEPENDENCIES) ===

    // Age: no transformations needed for integers
    #[Property(type: 'int', required: true)]
    public int $age;

    // Email: trim whitespace and convert to lowercase
    #[Property(type: 'string', required: true, transform: [TrimTransformer::class, LowercaseTransformer::class])]
    public string $email;

    // Country code: trim and convert to uppercase
    #[Property(type: 'string', required: false, transform: [TrimTransformer::class, UppercaseTransformer::class])]
    public ?string $country_code;

    // Password: hash the value (in real scenarios, use Laravel's Hash facade)
    #[Property(type: 'string', required: true, transform: [HashTransformer::class])]
    public string $password_hash;

    // Phone: just trim (no case transformation needed)
    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $phone;

    // Bio: simple trim
    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $bio;

    // === LEVEL 1: BASE FIELDS FOR TRANSFORMATIONS ===

    // Article title (trimmed) - needed for slug
    #[Property(type: 'string', required: true, transform: [TrimTransformer::class])]
    public string $title;

    // Individual name fields - needed for full_name
    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $first_name;

    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $last_name;

    // === LEVEL 2: FIELDS DEPENDENT ON LEVEL 1 ===

    // Slug generated automatically from title with length limit
    #[Property(
        type: 'string',
        required: false,
        transform: [TitleToSlugTransformer::class],
    )]
    public ?string $slug;

    // Full name generated from first and last name
    #[Property(
        type: 'string',
        required: false,
        transform: [FullNameTransformer::class],
    )]
    public ?string $full_name;

    // === LEVEL 3: FIELDS DEPENDENT ON LEVEL 2 ===

    // Username generated from full name (slug format with underscores)
    #[Property(
        type: 'string',
        required: false,
        transform: [UsernameTransformer::class],
    )]
    public ?string $username;
}
