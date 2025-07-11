<?php

declare(strict_types=1);

// Exemple d'utilisation des règles de validation personnalisées d'enum

require_once __DIR__ . '/vendor/autoload.php';

use Grazulex\LaravelArc\Support\Validation\Rules\InEnum;
use Grazulex\LaravelArc\Support\Validation\Rules\EnumExists;

// Enums d'exemple
enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

enum Priority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
}

enum Type
{
    case ADMIN;
    case USER;
    case GUEST;
}

// Test des règles InEnum
echo "=== Test InEnum Rule ===\n";

$statusRule = new InEnum(Status::class);
$priorityRule = new InEnum(Priority::class);
$typeRule = new InEnum(Type::class);

// Tests avec des valeurs valides
echo "Status 'active': " . ($statusRule->passes('status', 'active') ? "✓ PASS" : "✗ FAIL") . "\n";
echo "Priority 2: " . ($priorityRule->passes('priority', 2) ? "✓ PASS" : "✗ FAIL") . "\n";
echo "Type 'ADMIN': " . ($typeRule->passes('type', 'ADMIN') ? "✓ PASS" : "✗ FAIL") . "\n";

// Tests avec des valeurs invalides
echo "Status 'invalid': " . ($statusRule->passes('status', 'invalid') ? "✗ FAIL" : "✓ PASS") . "\n";
echo "Priority 5: " . ($priorityRule->passes('priority', 5) ? "✗ FAIL" : "✓ PASS") . "\n";
echo "Type 'INVALID': " . ($typeRule->passes('type', 'INVALID') ? "✗ FAIL" : "✓ PASS") . "\n";

// Test EnumExists Rule
echo "\n=== Test EnumExists Rule ===\n";

$statusExistsRule = new EnumExists(Status::class);
$priorityExistsRule = new EnumExists(Priority::class);
$typeExistsRule = new EnumExists(Type::class);

// Tests avec des valeurs valides
echo "Status 'pending': " . ($statusExistsRule->passes('status', 'pending') ? "✓ PASS" : "✗ FAIL") . "\n";
echo "Priority 1: " . ($priorityExistsRule->passes('priority', 1) ? "✓ PASS" : "✗ FAIL") . "\n";
echo "Type 'USER': " . ($typeExistsRule->passes('type', 'USER') ? "✓ PASS" : "✗ FAIL") . "\n";

// Tests avec des valeurs invalides
echo "Status 'cancelled': " . ($statusExistsRule->passes('status', 'cancelled') ? "✗ FAIL" : "✓ PASS") . "\n";
echo "Priority 0: " . ($priorityExistsRule->passes('priority', 0) ? "✗ FAIL" : "✓ PASS") . "\n";
echo "Type 'SUPERUSER': " . ($typeExistsRule->passes('type', 'SUPERUSER') ? "✗ FAIL" : "✓ PASS") . "\n";

// Messages d'erreur
echo "\n=== Messages d'erreur ===\n";
echo "InEnum message: " . $statusRule->message() . "\n";
echo "EnumExists message: " . $statusExistsRule->message() . "\n";

// Test avec des enums non existants
echo "\n=== Test avec des enums non existants ===\n";

$invalidRule = new InEnum('NonExistentEnum');
echo "Enum non existant: " . ($invalidRule->passes('field', 'value') ? "✗ FAIL" : "✓ PASS") . "\n";

$invalidExistsRule = new EnumExists('NonExistentEnum');
echo "Enum non existant (exists): " . ($invalidExistsRule->passes('field', 'value') ? "✗ FAIL" : "✓ PASS") . "\n";

echo "\n=== Démonstration terminée ===\n";
