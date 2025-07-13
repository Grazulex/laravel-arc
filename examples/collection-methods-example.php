<?php

declare(strict_types=1);

/**
 * Laravel Arc - Collection Methods Example
 *
 * This example demonstrates the new collection() method and DtoCollection capabilities.
 * Shows how to use the intuitive collection() alias and advanced collection features.
 */

use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;
use Grazulex\LaravelArc\Support\DtoCollection;

/**
 * Example Product DTO for demonstration
 */
final class ProductDto
{
    use ConvertsData;
    use DtoUtilities;
    use ValidatesData;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $category,
        public readonly float $price,
        public readonly int $stock,
        public readonly string $status = 'active',
        public readonly ?string $description = null,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            category: $model->category,
            price: $model->price,
            stock: $model->stock,
            status: $model->status ?? 'active',
            description: $model->description ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'price' => $this->price,
            'stock' => $this->stock,
            'status' => $this->status,
            'description' => $this->description,
        ];
    }
}

// Sample product data
$products = collect([
    (object) ['id' => 1, 'name' => 'Laptop Pro', 'category' => 'Electronics', 'price' => 1299.99, 'stock' => 15, 'status' => 'active', 'description' => 'High-performance laptop'],
    (object) ['id' => 2, 'name' => 'Wireless Mouse', 'category' => 'Electronics', 'price' => 29.99, 'stock' => 50, 'status' => 'active', 'description' => 'Ergonomic wireless mouse'],
    (object) ['id' => 3, 'name' => 'Office Chair', 'category' => 'Furniture', 'price' => 199.99, 'stock' => 8, 'status' => 'active', 'description' => 'Comfortable office chair'],
    (object) ['id' => 4, 'name' => 'Desk Lamp', 'category' => 'Furniture', 'price' => 49.99, 'stock' => 0, 'status' => 'out_of_stock', 'description' => 'LED desk lamp'],
    (object) ['id' => 5, 'name' => 'Keyboard', 'category' => 'Electronics', 'price' => 79.99, 'stock' => 25, 'status' => 'active', 'description' => 'Mechanical keyboard'],
    (object) ['id' => 6, 'name' => 'Monitor Stand', 'category' => 'Furniture', 'price' => 39.99, 'stock' => 12, 'status' => 'discontinued', 'description' => 'Adjustable monitor stand'],
]);

echo "Laravel Arc Collection Methods Example\n";
echo "=====================================\n\n";

// ========================================
// Collection Creation Methods
// ========================================

echo "1. COLLECTION CREATION METHODS\n";
echo "-------------------------------\n\n";

// Using the new collection() method (intuitive alias)
$productDtos1 = ProductDto::collection($products);
echo "Created collection using collection() method:\n";
echo "Type: " . get_class($productDtos1) . "\n";
echo "Count: " . $productDtos1->count() . "\n\n";

// Using the original fromModels() method
$productDtos2 = ProductDto::fromModels($products);
echo "Created collection using fromModels() method:\n";
echo "Type: " . get_class($productDtos2) . "\n";
echo "Count: " . $productDtos2->count() . "\n\n";

// Using fromModelsAsCollection() for standard Collection
$productDtos3 = ProductDto::fromModelsAsCollection($products);
echo "Created standard collection using fromModelsAsCollection():\n";
echo "Type: " . get_class($productDtos3) . "\n";
echo "Count: " . $productDtos3->count() . "\n\n";

echo "Note: collection() and fromModels() return DtoCollection with advanced features.\n";
echo "fromModelsAsCollection() returns standard Laravel Collection.\n\n";

// ========================================
// DtoCollection Advanced Features
// ========================================

echo "2. DTOCOLLECTION ADVANCED FEATURES\n";
echo "-----------------------------------\n\n";

// Use the collection() method for the rest of the examples
$productDtos = ProductDto::collection($products);

// API Resource Format
echo "API Resource Format:\n";
$apiFormat = $productDtos->toArrayResource(['total_products' => $productDtos->count()]);
echo json_encode($apiFormat, JSON_PRETTY_PRINT);
echo "\n\n";

// JSON Resource Format
echo "JSON Resource Format:\n";
echo $productDtos->toJsonResource(['timestamp' => date('Y-m-d H:i:s')]);
echo "\n\n";

// Filter by field
echo "Filter by category (Electronics):\n";
$electronics = $productDtos->whereField('category', 'Electronics');
echo "Found " . $electronics->count() . " electronics products\n";
foreach ($electronics as $product) {
    echo "- {$product->name} (\${$product->price})\n";
}
echo "\n";

// Group by field
echo "Group by category:\n";
$grouped = $productDtos->groupByField('category');
foreach ($grouped as $category => $products) {
    echo "Category: {$category} ({$products->count()} products)\n";
    foreach ($products as $product) {
        echo "  - {$product->name}\n";
    }
}
echo "\n";

// Pagination
echo "Paginated results (2 per page, page 1):\n";
$paginatedResults = $productDtos->paginate(2, 1);
echo json_encode($paginatedResults, JSON_PRETTY_PRINT);
echo "\n\n";

// Field selection
echo "Only specific fields from all products:\n";
$selectedFields = $productDtos->onlyFields(['id', 'name', 'price']);
foreach ($selectedFields as $product) {
    echo json_encode($product) . "\n";
}
echo "\n";

// Field exclusion
echo "Exclude specific fields from all products:\n";
$excludedFields = $productDtos->exceptFields(['description', 'stock']);
foreach ($excludedFields->take(2) as $product) {
    echo json_encode($product) . "\n";
}
echo "\n";

// ========================================
// Laravel Collection Methods
// ========================================

echo "3. LARAVEL COLLECTION METHODS\n";
echo "------------------------------\n\n";

// Standard Laravel Collection methods work on DtoCollection
echo "Active products (using where):\n";
$activeProducts = $productDtos->where('status', 'active');
echo "Count: " . $activeProducts->count() . "\n";

echo "Products sorted by price (ascending):\n";
$sortedByPrice = $productDtos->sortBy('price');
foreach ($sortedByPrice->take(3) as $product) {
    echo "- {$product->name}: \${$product->price}\n";
}
echo "\n";

echo "Products grouped by status:\n";
$groupedByStatus = $productDtos->groupBy('status');
foreach ($groupedByStatus as $status => $products) {
    echo "Status: {$status} ({$products->count()} products)\n";
}
echo "\n";

echo "Most expensive products (top 3):\n";
$expensive = $productDtos->sortByDesc('price')->take(3);
foreach ($expensive as $product) {
    echo "- {$product->name}: \${$product->price}\n";
}
echo "\n";

echo "Low stock products (< 10):\n";
$lowStock = $productDtos->filter(fn($product) => $product->stock < 10);
foreach ($lowStock as $product) {
    echo "- {$product->name}: {$product->stock} units\n";
}
echo "\n";

// ========================================
// Practical Usage Examples
// ========================================

echo "4. PRACTICAL USAGE EXAMPLES\n";
echo "----------------------------\n\n";

/**
 * Example Product Controller showing collection() usage
 */
class ProductController
{
    public function index()
    {
        // Get products from database (simulated)
        $products = collect([
            (object) ['id' => 1, 'name' => 'Laptop', 'category' => 'Electronics', 'price' => 999.99, 'stock' => 5, 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Chair', 'category' => 'Furniture', 'price' => 199.99, 'stock' => 10, 'status' => 'active'],
        ]);

        // Convert to DTOs using the intuitive collection() method
        $productDtos = ProductDto::collection($products);

        // Return as API resource
        return response()->json($productDtos->toArrayResource());
    }

    public function analytics()
    {
        $products = collect([
            (object) ['id' => 1, 'name' => 'Laptop', 'category' => 'Electronics', 'price' => 999.99, 'stock' => 5, 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Chair', 'category' => 'Furniture', 'price' => 199.99, 'stock' => 10, 'status' => 'active'],
        ]);

        $productDtos = ProductDto::collection($products);

        // Generate analytics using collection methods
        $analytics = [
            'total_products' => $productDtos->count(),
            'categories' => $productDtos->groupByField('category')->map->count(),
            'average_price' => $productDtos->avg('price'),
            'total_stock' => $productDtos->sum('stock'),
            'low_stock_products' => $productDtos->filter(fn($p) => $p->stock < 10)->count(),
        ];

        return response()->json($analytics);
    }

    public function export($format = 'json')
    {
        $products = collect([
            (object) ['id' => 1, 'name' => 'Laptop', 'category' => 'Electronics', 'price' => 999.99, 'stock' => 5, 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Chair', 'category' => 'Furniture', 'price' => 199.99, 'stock' => 10, 'status' => 'active'],
        ]);

        $productDtos = ProductDto::collection($products);

        return match ($format) {
            'json' => response()->json($productDtos->toArrayResource()),
            'csv' => response(ProductDto::collectionToCsv($products), 200, ['Content-Type' => 'text/csv']),
            'xml' => response(ProductDto::collectionToXml($products), 200, ['Content-Type' => 'application/xml']),
            'yaml' => response(ProductDto::collectionToYaml($products), 200, ['Content-Type' => 'application/yaml']),
            default => response()->json(['error' => 'Unsupported format'], 400),
        };
    }
}

echo "Example controller methods created above demonstrate:\n";
echo "- Using collection() method for intuitive model conversion\n";
echo "- Converting to API resource format\n";
echo "- Generating analytics with collection methods\n";
echo "- Exporting in multiple formats\n\n";

// ========================================
// Migration from Laravel Resources
// ========================================

echo "5. MIGRATION FROM LARAVEL RESOURCES\n";
echo "------------------------------------\n\n";

echo "Before (Laravel Resources):\n";
echo "```php\n";
echo "// UserResource.php\n";
echo "class UserResource extends JsonResource\n";
echo "{\n";
echo "    public function toArray(\$request) {\n";
echo "        return [\n";
echo "            'id' => \$this->id,\n";
echo "            'name' => \$this->name,\n";
echo "            'email' => \$this->email,\n";
echo "        ];\n";
echo "    }\n";
echo "}\n\n";
echo "// Controller\n";
echo "return UserResource::collection(\$users);\n";
echo "```\n\n";

echo "After (Laravel Arc DTOs):\n";
echo "```php\n";
echo "// UserDto.php (auto-generated from YAML)\n";
echo "// Contains strong typing, validation, and export methods\n\n";
echo "// Controller\n";
echo "return UserDto::collection(\$users)->toArrayResource();\n";
echo "```\n\n";

// ========================================
// Performance Comparison
// ========================================

echo "6. PERFORMANCE COMPARISON\n";
echo "-------------------------\n\n";

echo "DtoCollection vs Standard Collection:\n";
echo "- DtoCollection: Extended with API resource methods, field filtering, pagination\n";
echo "- Standard Collection: Basic Laravel collection functionality\n";
echo "- Performance: DtoCollection has minimal overhead over standard Collection\n\n";

echo "collection() vs fromModels():\n";
echo "- collection() is just an alias for fromModels()\n";
echo "- No performance difference\n";
echo "- collection() provides more intuitive API for developers familiar with Laravel Resources\n\n";

// ========================================
// Best Practices
// ========================================

echo "7. BEST PRACTICES\n";
echo "-----------------\n\n";

echo "1. Use collection() for intuitive model conversion:\n";
echo "   \$dtos = UserDto::collection(\$users);\n\n";

echo "2. Chain collection methods for complex operations:\n";
echo "   \$result = ProductDto::collection(\$products)\n";
echo "       ->whereField('status', 'active')\n";
echo "       ->sortBy('price')\n";
echo "       ->take(10)\n";
echo "       ->toArrayResource();\n\n";

echo "3. Use appropriate export format for your use case:\n";
echo "   - JSON: APIs and web responses\n";
echo "   - CSV: Data analysis and Excel\n";
echo "   - XML: Legacy systems and SOAP\n";
echo "   - YAML: Configuration files\n\n";

echo "4. Leverage DtoCollection for complex data operations:\n";
echo "   - groupByField() for categorization\n";
echo "   - onlyFields() for field selection\n";
echo "   - paginate() for pagination\n";
echo "   - whereField() for filtering\n\n";

echo "Example completed successfully!\n";