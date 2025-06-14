<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

/**
 * Example demonstrating the new clean Property syntax.
 * 
 * This syntax is more logical and consistent:
 * - type='enum', class=EnumClass::class
 * - type='date', format='Y-m-d' 
 * - type='nested', class=DTOClass::class
 * - type='collection', class=DTOClass::class
 */

// Define supporting classes
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
}

enum Priority
{
    case LOW;
    case NORMAL;
    case HIGH;
    case URGENT;
}

class AddressCleanDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $street;
    
    #[Property(type: 'string', required: true)]
    public string $city;
    
    #[Property(type: 'string', required: true)]
    public string $country;
}

class UserCleanDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $name;
    
    #[Property(type: 'string', required: true, validation: 'email')]
    public string $email;
    
    #[Property(type: 'int', required: true, validation: 'min:0|max:150')]
    public int $age;
    
    // Clean date syntax
    #[Property(type: 'date', required: false, format: 'Y-m-d')]
    public ?Carbon $birthDate;
    
    #[Property(type: 'date', required: false)]
    public ?Carbon $createdAt;
    
    // Clean enum syntax
    #[Property(type: 'enum', class: Priority::class, default: Priority::NORMAL)]
    public Priority $priority;
    
    // Clean nested DTO syntax
    #[Property(type: 'nested', class: AddressCleanDTO::class, required: false)]
    public ?AddressCleanDTO $address;
}

class OrderCleanDTO extends LaravelArcDTO
{
    #[Property(type: 'string', required: true)]
    public string $id;
    
    #[Property(type: 'float', required: true, validation: 'min:0')]
    public float $amount;
    
    // Clean enum syntax - much more logical!
    #[Property(type: 'enum', class: OrderStatus::class, required: true)]
    public OrderStatus $status;
    
    #[Property(type: 'enum', class: Priority::class, default: Priority::NORMAL)]
    public Priority $priority;
    
    // Clean collection syntax
    #[Property(type: 'collection', class: UserCleanDTO::class, required: false)]
    public array $customers;
    
    #[Property(type: 'date', required: true)]
    public Carbon $createdAt;
    
    #[Property(type: 'date', required: false)]
    public ?Carbon $shippedAt;
}

/**
 * Comparison of syntaxes:
 * 
 * OLD (redundant):
 * #[Property('UserStatus', enumClass: UserStatus::class)]
 * #[Property('Carbon', required: false)]
 * #[Property('AddressDTO', dtoClass: AddressDTO::class)]
 * #[Property('array<UserDTO>')]
 * 
 * NEW (clean and logical):
 * #[Property(type: 'enum', class: UserStatus::class)]
 * #[Property(type: 'date', required: false)]
 * #[Property(type: 'nested', class: AddressDTO::class)]
 * #[Property(type: 'collection', class: UserDTO::class)]
 * 
 * Benefits:
 * ✅ No redundancy - specify the class only once
 * ✅ Clear intent - 'enum', 'date', 'nested', 'collection' are explicit
 * ✅ Consistent pattern - same syntax for all complex types
 * ✅ Better readability - easier to understand at a glance
 * ✅ IDE friendly - better autocomplete and validation
 */

