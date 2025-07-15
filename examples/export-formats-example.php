<?php

declare(strict_types=1);

/**
 * Laravel Arc - Export Formats Example
 *
 * This example demonstrates how to use the new export formats available in Laravel Arc DTO.
 * All export methods are available on both single DTO and collections.
 */

use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

/**
 * Example User DTO with all export capabilities
 */
final class UserDto
{
    use ConvertsData;
    use DtoUtilities;
    use ValidatesData;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $status = 'active',
        public readonly string $role = 'user',
        public readonly ?string $city = null,
        public readonly ?string $country = null,
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            status: $model->status ?? 'active',
            role: $model->role ?? 'user',
            city: $model->city ?? null,
            country: $model->country ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'role' => $this->role,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }
}

// Sample data for demonstration
$users = collect([
    (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active', 'role' => 'admin', 'city' => 'New York', 'country' => 'USA'],
    (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'active', 'role' => 'user', 'city' => 'London', 'country' => 'UK'],
    (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'inactive', 'role' => 'user', 'city' => 'Toronto', 'country' => 'Canada'],
    (object) ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'status' => 'active', 'role' => 'moderator', 'city' => 'Sydney', 'country' => 'Australia'],
    (object) ['id' => 5, 'name' => 'Charlie Wilson', 'email' => 'charlie@example.com', 'status' => 'pending', 'role' => 'user', 'city' => 'Berlin', 'country' => 'Germany'],
]);

echo "Laravel Arc Export Formats Example\n";
echo "==================================\n\n";

// ========================================
// Single DTO Export Examples
// ========================================

$singleUser = UserDto::fromModel($users->first());

echo "1. SINGLE DTO EXPORT FORMATS\n";
echo "-----------------------------\n\n";

// JSON Export
echo "JSON Export:\n";
echo $singleUser->toJson(JSON_PRETTY_PRINT);
echo "\n\n";

// YAML Export
echo "YAML Export:\n";
echo $singleUser->toYaml();
echo "\n";

// CSV Export
echo "CSV Export (with headers):\n";
echo $singleUser->toCsv();
echo "\n\n";

// XML Export
echo "XML Export:\n";
echo $singleUser->toXml('user');
echo "\n\n";

// TOML Export
echo "TOML Export:\n";
echo $singleUser->toToml();
echo "\n";

// Markdown Table Export
echo "Markdown Table Export:\n";
echo $singleUser->toMarkdownTable();
echo "\n";

// PHP Array Export
echo "PHP Array Export:\n";
echo $singleUser->toPhpArray();
echo "\n\n";

// Query String Export
echo "Query String Export:\n";
echo $singleUser->toQueryString();
echo "\n\n";

// MessagePack Export (if available)
echo "MessagePack Export:\n";
try {
    $packed = $singleUser->toMessagePack();
    echo 'Binary data length: '.mb_strlen($packed)." bytes\n";
    echo "Successfully packed to MessagePack format\n";
} catch (RuntimeException $e) {
    echo 'MessagePack extension not available: '.$e->getMessage()."\n";
}
echo "\n";

// Collection Export
echo "Collection Export (Laravel Collection):\n";
$collection = $singleUser->toCollection();
echo 'Collection type: '.get_class($collection)."\n";
echo 'Collection count: '.$collection->count()."\n";
echo 'Keys: '.$collection->keys()->implode(', ')."\n";
echo "\n";

// Field Selection
echo "Field Selection (only specific fields):\n";
var_export($singleUser->only(['id', 'name', 'email']));
echo "\n\n";

echo "Field Exclusion (except specific fields):\n";
var_export($singleUser->except(['id', 'status']));
echo "\n\n";

// ========================================
// Collection Export Examples
// ========================================

echo "2. COLLECTION EXPORT FORMATS\n";
echo "-----------------------------\n\n";

// JSON Collection Export
echo "JSON Collection Export:\n";
echo UserDto::collectionToJson($users);
echo "\n\n";

// YAML Collection Export
echo "YAML Collection Export:\n";
echo UserDto::collectionToYaml($users);
echo "\n";

// CSV Collection Export
echo "CSV Collection Export:\n";
echo UserDto::collectionToCsv($users);
echo "\n\n";

// XML Collection Export
echo "XML Collection Export:\n";
echo UserDto::collectionToXml($users, 'users', 'user');
echo "\n\n";

// Markdown Table Collection Export
echo "Markdown Table Collection Export:\n";
echo UserDto::collectionToMarkdownTable($users);
echo "\n";

// ========================================
// Advanced Usage Examples
// ========================================

echo "3. ADVANCED USAGE EXAMPLES\n";
echo "---------------------------\n\n";

// Filter and export
echo "Filtered Export (active users only):\n";
$activeUsers = $users->filter(fn ($user) => $user->status === 'active');
echo UserDto::collectionToMarkdownTable($activeUsers);
echo "\n";

// Export with different delimiters
echo "CSV with different delimiter (semicolon):\n";
echo UserDto::collectionToCsv($users, ';');
echo "\n\n";

// Export without headers
echo "CSV without headers:\n";
echo UserDto::collectionToCsv($users, ',', '"', '\\', false);
echo "\n\n";

// ========================================
// Real-world Controller Usage
// ========================================

echo "4. REAL-WORLD CONTROLLER USAGE\n";
echo "-------------------------------\n\n";

/**
 * Example showing how to use exports in a Laravel controller
 */
final class UserExportController
{
    public function export($format = 'json')
    {
        // This would typically come from your database
        $users = collect([
            (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
            (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'active'],
        ]);

        return match ($format) {
            'json' => response()->json(['data' => UserDto::fromModels($users)->toArray()]),
            'csv' => response(
                UserDto::collectionToCsv($users),
                200,
                [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="users.csv"',
                ]
            ),
            'xml' => response(
                UserDto::collectionToXml($users, 'users', 'user'),
                200,
                ['Content-Type' => 'application/xml']
            ),
            'yaml' => response(
                UserDto::collectionToYaml($users),
                200,
                ['Content-Type' => 'application/yaml']
            ),
            'markdown' => response(
                "# Users Export\n\n".UserDto::collectionToMarkdownTable($users),
                200,
                ['Content-Type' => 'text/markdown']
            ),
            default => response()->json(['error' => 'Unsupported format'], 400),
        };
    }
}

echo "Example controller method created above shows how to:\n";
echo "- Export to multiple formats based on request parameter\n";
echo "- Set appropriate Content-Type headers\n";
echo "- Handle file downloads for CSV\n";
echo "- Return appropriate error responses\n\n";

// ========================================
// Performance and Usage Tips
// ========================================

echo "5. PERFORMANCE AND USAGE TIPS\n";
echo "------------------------------\n\n";

echo "Format Recommendations:\n";
echo "- JSON: Best for APIs, smallest payload\n";
echo "- CSV: Best for data analysis, Excel compatibility\n";
echo "- XML: Good for legacy systems, SOAP services\n";
echo "- YAML: Human-readable, good for configuration\n";
echo "- TOML: Modern configuration format\n";
echo "- Markdown: Perfect for documentation\n";
echo "- MessagePack: Most efficient binary format (requires extension)\n";
echo "- Collection: For further processing with Laravel Collection methods\n\n";

echo "Extension Requirements:\n";
echo "- YAML: php-yaml extension (fallback available)\n";
echo "- MessagePack: php-msgpack extension (throws exception if not available)\n\n";

// ========================================
// Collection vs Single DTO Methods
// ========================================

echo "6. COLLECTION VS SINGLE DTO METHODS\n";
echo "------------------------------------\n\n";

echo "Collection methods (static):\n";
echo "- UserDto::collectionToJson(\$models)\n";
echo "- UserDto::collectionToYaml(\$models)\n";
echo "- UserDto::collectionToCsv(\$models)\n";
echo "- UserDto::collectionToXml(\$models)\n";
echo "- UserDto::collectionToMarkdownTable(\$models)\n\n";

echo "Single DTO methods (instance):\n";
echo "- \$dto->toJson()\n";
echo "- \$dto->toYaml()\n";
echo "- \$dto->toCsv()\n";
echo "- \$dto->toXml()\n";
echo "- \$dto->toToml()\n";
echo "- \$dto->toMarkdownTable()\n";
echo "- \$dto->toPhpArray()\n";
echo "- \$dto->toQueryString()\n";
echo "- \$dto->toMessagePack()\n";
echo "- \$dto->toCollection()\n\n";

echo "Collection creation methods:\n";
echo "- UserDto::collection(\$models) // Intuitive alias\n";
echo "- UserDto::fromModels(\$models) // Original method\n";
echo "- UserDto::fromModelsAsCollection(\$models) // Standard Collection\n\n";

echo "Example completed successfully!\n";
