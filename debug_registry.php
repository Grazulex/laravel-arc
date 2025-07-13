<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Grazulex\LaravelArc\Support\Traits\Behavioral\BehavioralTraitRegistry;

echo "=== Testing BehavioralTraitRegistry ===\n\n";

// Test registering and getting traits
echo "Getting traits:\n";
$traits = BehavioralTraitRegistry::getTraits();
print_r(array_keys($traits));

echo "\nTrying to get HasTimestamps:\n";
try {
    $info = BehavioralTraitRegistry::getTraitInfo('HasTimestamps');
    echo "Found: $info\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

echo "\nTrying to get HasUuid:\n";
try {
    $info = BehavioralTraitRegistry::getTraitInfo('HasUuid');
    echo "Found: $info\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

echo "\nTesting expandFields:\n";
try {
    $fields = ['name' => ['type' => 'string']];
    $traits = ['HasTimestamps'];
    $result = BehavioralTraitRegistry::expandFields($fields, $traits);
    echo "Expanded fields:\n";
    print_r(array_keys($result));
} catch (Exception $e) {
    echo 'Error in expandFields: '.$e->getMessage()."\n";
    echo 'Trace: '.$e->getTraceAsString()."\n";
}
