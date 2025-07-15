<?php

declare(strict_types=1);

/**
 * Laravel Arc - Export Interface Example
 *
 * This example demonstrates the complete export interface provided by Laravel Arc DTO.
 * It shows both single DTO exports and collection exports with all available formats.
 */

use Grazulex\LaravelArc\Support\DtoCollection;
use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

/**
 * Example Product DTO demonstrating the export interface
 */
final class ProductDto
{
    use ConvertsData;
    use DtoUtilities;
    use ValidatesData;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly float $price,
        public readonly string $category,
        public readonly bool $active = true,
        public readonly ?string $image = null,
        public readonly array $tags = [],
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            price: $model->price,
            category: $model->category,
            active: $model->active ?? true,
            image: $model->image,
            tags: $model->tags ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category' => $this->category,
            'active' => $this->active,
            'image' => $this->image,
            'tags' => $this->tags,
        ];
    }
}

// Sample data for demonstration
$products = collect([
    (object) [
        'id' => 1,
        'name' => 'Laptop Pro',
        'description' => 'High-performance laptop for professionals',
        'price' => 1299.99,
        'category' => 'electronics',
        'active' => true,
        'image' => '/images/laptop-pro.jpg',
        'tags' => ['laptop', 'professional', 'high-performance'],
    ],
    (object) [
        'id' => 2,
        'name' => 'Wireless Mouse',
        'description' => 'Ergonomic wireless mouse with precision tracking',
        'price' => 29.99,
        'category' => 'accessories',
        'active' => true,
        'image' => '/images/wireless-mouse.jpg',
        'tags' => ['mouse', 'wireless', 'ergonomic'],
    ],
    (object) [
        'id' => 3,
        'name' => 'Mechanical Keyboard',
        'description' => 'Premium mechanical keyboard with RGB backlighting',
        'price' => 149.99,
        'category' => 'accessories',
        'active' => false,
        'image' => '/images/mechanical-keyboard.jpg',
        'tags' => ['keyboard', 'mechanical', 'rgb'],
    ],
]);

echo "Laravel Arc Export Interface Demonstration\n";
echo "==========================================\n\n";

// ========================================
// Single DTO Export Interface
// ========================================

$singleProduct = ProductDto::fromModel($products->first());

echo "1. SINGLE DTO EXPORT INTERFACE\n";
echo "-------------------------------\n\n";

echo "All available export methods for single DTO:\n";
echo "- toJson(int \$options = 0): string\n";
echo "- toYaml(): string\n";
echo "- toCsv(string \$delimiter = ',', string \$enclosure = '\"', string \$escape = '\\', bool \$includeHeaders = true): string\n";
echo "- toXml(string \$rootElement = 'dto', string \$encoding = 'UTF-8'): string\n";
echo "- toToml(): string\n";
echo "- toMarkdownTable(bool \$includeHeaders = true): string\n";
echo "- toPhpArray(): string\n";
echo "- toQueryString(): string\n";
echo "- toMessagePack(): string\n";
echo "- toCollection(): Collection\n";
echo "\n";

// JSON Export with options
echo "JSON Export (with pretty print):\n";
echo $singleProduct->toJson(JSON_PRETTY_PRINT);
echo "\n\n";

// YAML Export
echo "YAML Export:\n";
echo $singleProduct->toYaml();
echo "\n";

// CSV Export
echo "CSV Export:\n";
echo $singleProduct->toCsv();
echo "\n\n";

// XML Export with custom root element
echo "XML Export (custom root element):\n";
echo $singleProduct->toXml('product');
echo "\n\n";

// TOML Export
echo "TOML Export:\n";
echo $singleProduct->toToml();
echo "\n";

// Markdown Table Export
echo "Markdown Table Export:\n";
echo $singleProduct->toMarkdownTable();
echo "\n";

// PHP Array Export
echo "PHP Array Export:\n";
echo $singleProduct->toPhpArray();
echo "\n\n";

// Query String Export
echo "Query String Export:\n";
echo $singleProduct->toQueryString();
echo "\n\n";

// MessagePack Export (with error handling)
echo "MessagePack Export:\n";
try {
    $packed = $singleProduct->toMessagePack();
    echo '✅ Successfully packed to MessagePack format ('.mb_strlen($packed)." bytes)\n";
} catch (RuntimeException $e) {
    echo '❌ MessagePack extension not available: '.$e->getMessage()."\n";
}
echo "\n";

// Collection Export
echo "Collection Export:\n";
$collection = $singleProduct->toCollection();
echo '✅ Converted to Laravel Collection ('.get_class($collection).")\n";
echo '   Keys: '.$collection->keys()->implode(', ')."\n";
echo "   Values can be further processed with Collection methods\n";
echo "\n";

// ========================================
// Collection Export Interface
// ========================================

echo "2. COLLECTION EXPORT INTERFACE\n";
echo "-------------------------------\n\n";

echo "All available collection export methods:\n";
echo "- ProductDto::collectionToJson(iterable \$models): string\n";
echo "- ProductDto::collectionToYaml(iterable \$models): string\n";
echo "- ProductDto::collectionToCsv(iterable \$models, string \$delimiter = ',', string \$enclosure = '\"', string \$escape = '\\', bool \$includeHeaders = true): string\n";
echo "- ProductDto::collectionToXml(iterable \$models, string \$rootElement = 'collection', string \$itemElement = 'item', string \$encoding = 'UTF-8'): string\n";
echo "- ProductDto::collectionToMarkdownTable(iterable \$models, bool \$includeHeaders = true): string\n";
echo "\n";

// JSON Collection Export
echo "JSON Collection Export:\n";
echo ProductDto::collectionToJson($products);
echo "\n\n";

// YAML Collection Export
echo "YAML Collection Export:\n";
echo ProductDto::collectionToYaml($products);
echo "\n";

// CSV Collection Export
echo "CSV Collection Export:\n";
echo ProductDto::collectionToCsv($products);
echo "\n\n";

// XML Collection Export with custom elements
echo "XML Collection Export (custom elements):\n";
echo ProductDto::collectionToXml($products, 'products', 'product');
echo "\n\n";

// Markdown Table Collection Export
echo "Markdown Table Collection Export:\n";
echo ProductDto::collectionToMarkdownTable($products);
echo "\n";

// ========================================
// DtoCollection Interface
// ========================================

echo "3. DTOCOLLECTION INTERFACE\n";
echo "---------------------------\n\n";

$productDtos = ProductDto::collection($products);

echo "DtoCollection methods (similar to Laravel API Resources):\n";
echo "- toArrayResource(array \$meta = []): array\n";
echo "- toJsonResource(array \$meta = []): string\n";
echo "- Plus all standard Laravel Collection methods\n";
echo "\n";

// API Resource format
echo "API Resource format:\n";
echo json_encode($productDtos->toArrayResource(['total' => $products->count()]), JSON_PRETTY_PRINT);
echo "\n\n";

// JSON Resource format
echo "JSON Resource format:\n";
echo $productDtos->toJsonResource(['total' => $products->count()]);
echo "\n\n";

// ========================================
// Interface Usage in Practice
// ========================================

echo "4. PRACTICAL INTERFACE USAGE\n";
echo "-----------------------------\n\n";

// Example: Export based on user preference
function exportProducts(iterable $products, string $format = 'json'): string
{
    return match ($format) {
        'json' => ProductDto::collectionToJson($products),
        'csv' => ProductDto::collectionToCsv($products),
        'xml' => ProductDto::collectionToXml($products, 'products', 'product'),
        'yaml' => ProductDto::collectionToYaml($products),
        'markdown' => ProductDto::collectionToMarkdownTable($products),
        default => throw new InvalidArgumentException("Unsupported format: {$format}"),
    };
}

echo "Dynamic export based on format:\n";
foreach (['json', 'csv', 'xml', 'yaml', 'markdown'] as $format) {
    echo "- {$format}: ".mb_strlen(exportProducts($products, $format))." bytes\n";
}
echo "\n";

// Example: Single DTO export with error handling
function exportSingleProduct(ProductDto $product, string $format = 'json'): string
{
    try {
        return match ($format) {
            'json' => $product->toJson(),
            'yaml' => $product->toYaml(),
            'csv' => $product->toCsv(),
            'xml' => $product->toXml('product'),
            'toml' => $product->toToml(),
            'markdown' => $product->toMarkdownTable(),
            'php' => $product->toPhpArray(),
            'query' => $product->toQueryString(),
            'msgpack' => $product->toMessagePack(),
            default => throw new InvalidArgumentException("Unsupported format: {$format}"),
        };
    } catch (RuntimeException $e) {
        return 'Export failed: '.$e->getMessage();
    }
}

echo "Single DTO export with error handling:\n";
foreach (['json', 'yaml', 'csv', 'xml', 'toml', 'markdown', 'php', 'query', 'msgpack'] as $format) {
    $result = exportSingleProduct($singleProduct, $format);
    $status = str_contains($result, 'failed') ? '❌' : '✅';
    echo "{$status} {$format}: ".(str_contains($result, 'failed') ? $result : mb_strlen($result).' bytes')."\n";
}
echo "\n";

echo "🎉 Laravel Arc Export Interface Demonstration Complete!\n";
echo "For more examples, check out the other files in the examples/ directory.\n";
