<?php

namespace Grazulex\Arc\Examples;

use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\Interfaces\TransformerInterface;
use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Transformers\SlugTransformer;
use Grazulex\Arc\Transformers\TrimTransformer;

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
 * Example transformer for creating username from full name.
 */
class NameToUsernameTransformer extends SlugTransformer
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
 * Example DTO showcasing SlugTransformer with cross-field transformations.
 *
 * This demonstrates:
 * - Slug generation from title field
 * - Username generation from full name
 * - Full name generation from first/last name fields
 * - Context-aware transformations between fields
 */
class BlogPostDTO extends LaravelArcDTO
{
    // === BASIC FIELDS ===

    #[Property(type: 'string', required: true, transform: [TrimTransformer::class])]
    public string $title;

    // Slug automatically generated from title
    #[Property(
        type: 'string',
        required: false,
        transform: [TitleToSlugTransformer::class],
    )]
    public ?string $slug;

    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $content;

    // === AUTHOR INFORMATION ===

    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $first_name;

    #[Property(type: 'string', required: false, transform: [TrimTransformer::class])]
    public ?string $last_name;

    // Full name generated from first + last name
    #[Property(
        type: 'string',
        required: false,
        transform: [FullNameTransformer::class],
    )]
    public ?string $full_name;

    // Username generated from full name (slug format)
    #[Property(
        type: 'string',
        required: false,
        transform: [NameToUsernameTransformer::class],
    )]
    public ?string $username;

    // === META FIELDS ===

    #[Property(type: 'bool', required: false, default: false)]
    public bool $published;

    #[Property(type: 'array', required: false, default: [])]
    public array $tags;
}

// Usage examples:

/*
// Example 1: Basic slug generation
$post = new BlogPostDTO([
    'title' => 'My First Blog Post',
    'content' => 'This is the content...'
]);
echo $post->slug; // Output: 'my-first-blog-post'

// Example 2: Author with username generation
$post = new BlogPostDTO([
    'title' => 'Advanced PHP Techniques',
    'first_name' => 'Jean-Marc',
    'last_name' => 'Strauven',
    'content' => 'Let me show you...'
]);
echo $post->slug;      // Output: 'advanced-php-techniques'
echo $post->full_name; // Output: 'Jean-Marc Strauven'
echo $post->username;  // Output: 'jean_marc_strauven'

// Example 3: Long title with length limit
$post = new BlogPostDTO([
    'title' => 'This is an Extremely Long Blog Post Title That Will Be Truncated Because It Exceeds the Maximum Length'
]);
echo $post->slug; // Output: 'this-is-an-extremely-long-blog-post-title' (truncated at 50 chars)

// Example 4: Partial names
$post = new BlogPostDTO([
    'title' => 'Guest Post',
    'first_name' => 'Alice'
    // last_name not provided
]);
echo $post->full_name; // Output: 'Alice'
echo $post->username;  // Output: 'alice'
*/
