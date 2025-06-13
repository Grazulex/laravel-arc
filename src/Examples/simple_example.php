<?php

/**
 * Simple usage example of Laravel Arc DTO
 */

require_once '../../vendor/autoload.php';

use Grazulex\Arc\LaravelArcDTO;
use Grazulex\Arc\Attributes\Property;

// Simple DTO definition
class ProductDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true, validation: 'max:100')]
    public string $name;

    #[Property(type: 'float', required: true, validation: 'min:0')]
    public float $price;

    #[Property(type: 'string', required: false, default: 'available')]
    public string $status;

    #[Property(type: 'array', required: false, default: [])]
    public array $tags;
}

echo "=== Simple Laravel Arc DTO Example ===\n\n";

// Creating a product
$product = new ProductDTO([
    'name' => 'Dell Laptop',
    'price' => 999.99
]);

echo "Product created:\n";
echo "- Name: {$product->name}\n";           // Direct access
echo "- Price: €{$product->price}\n";
echo "- Status: {$product->status}\n";     // Default value
echo "- Tags: " . json_encode($product->tags) . "\n\n";

// Direct modification
$product->status = 'sold';
$product->tags = ['electronics', 'computer', 'dell'];

echo "After modification:\n";
echo "- Status: {$product->status}\n";
echo "- Tags: " . implode(', ', $product->tags) . "\n\n";

// Array conversion
echo "Complete data:\n";
print_r($product->toArray());

echo "\nAutomatically generated validation rules:\n";
print_r(ProductDTO::rules());

