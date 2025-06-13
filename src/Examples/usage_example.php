<?php

/**
 * Usage example of the new DTO system with attributes.
 */

require_once '../../vendor/autoload.php';

use Grazulex\Arc\Examples\ModernUserDTO;
use Grazulex\Arc\Exceptions\InvalidDTOException;

// Creating a DTO with automatic validation
try {
    $user = new ModernUserDTO([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30,
        // 'role' and 'active' will have their default values
        // 'permissions' will be an empty array by default
    ]);

    // Direct property access - exactly what you wanted!
    echo 'Name: ' . $user->name . "\n";
    echo 'Email: ' . $user->email . "\n";
    echo 'Age: ' . $user->age . "\n";
    echo 'Role: ' . $user->role . "\n"; // Default value: 'user'
    echo 'Active: ' . ($user->active ? 'Yes' : 'No') . "\n"; // Default value: true

    // Direct property assignment
    $user->role = 'admin';
    $user->permissions = ['read', 'write', 'delete'];

    echo "\nAfter modification:\n";
    echo 'Role: ' . $user->role . "\n";
    echo 'Permissions: ' . implode(', ', $user->permissions) . "\n";

    // Getters/setters remain available for compatibility if needed
    echo "\nGetters/setters compatibility (optional):\n";
    echo 'Name via getter: ' . $user->getName() . "\n";
    $user->setAge(31);
    echo 'Age after setter: ' . $user->age . "\n";

    // Array conversion
    echo "\nComplete data:\n";
    print_r($user->toArray());

    // Validation rules are generated automatically
    echo "\nGenerated validation rules:\n";
    print_r(ModernUserDTO::rules());
} catch (InvalidDTOException $e) {
    echo 'Validation error: ' . $e->getMessage() . "\n";
    if (!empty($e->getErrors())) {
        echo "Error details:\n";
        print_r($e->getErrors());
    }
}

// Example with invalid data
try {
    $invalidUser = new ModernUserDTO([
        'name' => '', // Required but empty
        'email' => 'invalid-email', // Invalid email format
        'age' => -5, // Negative age
    ]);
} catch (InvalidDTOException $e) {
    echo "\nExpected error with invalid data: " . $e->getMessage() . "\n";
    if (!empty($e->getErrors())) {
        echo "Error details:\n";
        foreach ($e->getErrors() as $field => $errors) {
            echo "  - {$field}: " . implode(', ', $errors) . "\n";
        }
    }
}
