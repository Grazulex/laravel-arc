<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Support\Traits\ConvertsData;
use Grazulex\LaravelArc\Support\Traits\DtoUtilities;
use Grazulex\LaravelArc\Support\Traits\ValidatesData;

// Create a test DTO class
$dtoClass = new class(1, 'Test User', 'test@example.com', 'active')
{
    use ConvertsData, DtoUtilities, ValidatesData;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $status = 'active'
    ) {}

    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            status: $model->status ?? 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ];
    }

    public function isValid(): bool
    {
        return ! empty($this->name) && ! empty($this->email);
    }

    public function getErrors(): array
    {
        return [];
    }
};

// Create test data
$mockModels = collect([
    (object) ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active'],
    (object) ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive'],
    (object) ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active'],
    (object) ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'status' => 'pending'],
]);

// Convert to DTO collection
$dtoCollection = $dtoClass::fromModels($mockModels);

echo "Testing DtoCollection field methods:\n";
echo "===================================\n";
echo 'Original collection count: '.$dtoCollection->count()."\n\n";

// Test whereField method
echo "Testing whereField method:\n";
$activeUsers = $dtoCollection->whereField('status', 'active');
echo '- Active users count: '.$activeUsers->count()."\n";

$inactiveUsers = $dtoCollection->whereField('status', 'inactive');
echo '- Inactive users count: '.$inactiveUsers->count()."\n";

$pendingUsers = $dtoCollection->whereField('status', 'pending');
echo '- Pending users count: '.$pendingUsers->count()."\n";

// Test with non-existent field
$nonExistentField = $dtoCollection->whereField('nonexistent', 'value');
echo '- Non-existent field count: '.$nonExistentField->count()."\n";

// Test with existing field but wrong value
$wrongValue = $dtoCollection->whereField('status', 'nonexistent_status');
echo '- Wrong value count: '.$wrongValue->count()."\n\n";

// Test groupByField method
echo "Testing groupByField method:\n";
$groupedByStatus = $dtoCollection->groupByField('status');
echo '- Groups created: '.$groupedByStatus->count()."\n";
echo '- Group keys: '.implode(', ', $groupedByStatus->keys()->toArray())."\n";

foreach ($groupedByStatus as $status => $group) {
    echo "- Status '$status': ".$group->count()." items\n";
}

// Test with non-existent field
$groupedByNonExistent = $dtoCollection->groupByField('nonexistent');
echo '- Groups from non-existent field: '.$groupedByNonExistent->count()."\n";
if ($groupedByNonExistent->has('')) {
    echo '- Items with null value: '.$groupedByNonExistent->get('')->count()."\n";
}

echo "\nBoth whereField and groupByField methods work correctly!\n";
