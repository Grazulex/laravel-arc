<?php

declare(strict_types=1);

/**
 * Script de démonstration des règles personnalisées pour les enums
 * Ce script montre comment utiliser les règles in_enum et enum_exists
 * avec des classes enum PHP 8.0 natives.
 */

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Support\Validation\Rules\EnumExists;
use Grazulex\LaravelArc\Support\Validation\Rules\InEnum;

// Définition d'enums de démonstration
enum DemoStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case COMPLETED = 'completed';
}

enum DemoPriority
{
    case LOW;
    case MEDIUM;
    case HIGH;
    case URGENT;
}

echo "=== Démonstration des règles personnalisées pour les enums ===\n\n";

// Test de la règle InEnum
echo "1. Test de la règle InEnum avec DemoStatus (enum backed)\n";
echo "   - Classe enum : DemoStatus\n";
echo '   - Valeurs valides : ';
foreach (DemoStatus::cases() as $case) {
    echo $case->value.' ';
}
echo "\n\n";

$inEnumRule = new InEnum(DemoStatus::class);

// Test avec une valeur valide
$validValue = 'active';
echo "   Test avec valeur valide '$validValue' : ";
echo $inEnumRule->passes('status', $validValue) ? '✓ PASS' : '✗ FAIL';
echo "\n";

// Test avec une valeur invalide
$invalidValue = 'invalid';
echo "   Test avec valeur invalide '$invalidValue' : ";
echo $inEnumRule->passes('status', $invalidValue) ? '✓ PASS' : '✗ FAIL';
echo "\n";
echo "   Message d'erreur : ".$inEnumRule->message()."\n\n";

// Test de la règle EnumExists
echo "2. Test de la règle EnumExists avec DemoPriority (enum pure)\n";
echo "   - Classe enum : DemoPriority\n";
echo '   - Valeurs valides : ';
foreach (DemoPriority::cases() as $case) {
    echo $case->name.' ';
}
echo "\n\n";

$enumExistsRule = new EnumExists(DemoPriority::class);

// Test avec une valeur valide
$validValue = 'HIGH';
echo "   Test avec valeur valide '$validValue' : ";
echo $enumExistsRule->passes('priority', $validValue) ? '✓ PASS' : '✗ FAIL';
echo "\n";

// Test avec une valeur invalide
$invalidValue = 'INVALID';
echo "   Test avec valeur invalide '$invalidValue' : ";
echo $enumExistsRule->passes('priority', $invalidValue) ? '✓ PASS' : '✗ FAIL';
echo "\n";
echo "   Message d'erreur : ".$enumExistsRule->message()."\n\n";

// Test avec une classe qui n'existe pas
echo "3. Test avec une classe enum inexistante\n";
$invalidEnumRule = new InEnum('App\\Enums\\NonExistentEnum');
echo '   Test avec classe inexistante : ';
echo $invalidEnumRule->passes('field', 'any_value') ? '✓ PASS' : '✗ FAIL';
echo "\n";
echo "   Message d'erreur : ".$invalidEnumRule->message()."\n\n";

// Test avec une classe qui n'est pas un enum
echo "4. Test avec une classe qui n'est pas un enum\n";
$notEnumRule = new InEnum('stdClass');
echo '   Test avec classe non-enum : ';
echo $notEnumRule->passes('field', 'any_value') ? '✓ PASS' : '✗ FAIL';
echo "\n";
echo "   Message d'erreur : ".$notEnumRule->message()."\n\n";

echo "=== Validation des règles combinées ===\n";

// Test avec enum backed et règle InEnum
echo "5. Test complet avec enum backed (DemoStatus)\n";
$statusRule = new InEnum(DemoStatus::class);
$testValues = ['pending', 'active', 'invalid', 'completed'];
foreach ($testValues as $value) {
    echo "   '$value' : ";
    echo $statusRule->passes('status', $value) ? '✓ PASS' : '✗ FAIL';
    echo "\n";
}

echo "\n6. Test complet avec enum pure (DemoPriority)\n";
$priorityRule = new EnumExists(DemoPriority::class);
$testValues = ['LOW', 'MEDIUM', 'INVALID', 'HIGH', 'URGENT'];
foreach ($testValues as $value) {
    echo "   '$value' : ";
    echo $priorityRule->passes('priority', $value) ? '✓ PASS' : '✗ FAIL';
    echo "\n";
}

echo "\n=== Fin de la démonstration ===\n";
echo "\nPour utiliser ces règles dans vos définitions YAML :\n";
echo "fields:\n";
echo "  status:\n";
echo "    type: enum\n";
echo "    class: App\\Enums\\Status\n";
echo "    rules:\n";
echo "      - in_enum\n";
echo "      - enum_exists\n";
echo "      - required\n";
