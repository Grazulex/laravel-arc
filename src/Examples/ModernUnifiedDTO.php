<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Grazulex\Arc\Abstract\AbstractDTO;
use Grazulex\Arc\Attributes\Property;

/**
 * Example DTO demonstrating the new unified Property attribute.
 *
 * This shows how you can now use a single Property attribute for all types,
 * with intelligent auto-detection of cast types.
 */
class ModernUnifiedDTO extends AbstractDTO
{
    // Basic types - automatically detected
    #[Property('string', required: true)]
    public string $name;

    #[Property('int', default: 0)]
    public int $age;

    #[Property('float', default: 0.0)]
    public float $score;

    #[Property('bool', default: false)]
    public bool $isActive;

    #[Property('array', default: [])]
    public array $tags;

    // Date - automatically detected as Carbon
    #[Property('Carbon', format: 'Y-m-d', timezone: 'UTC')]
    public ?Carbon $birthDate;

    #[Property('CarbonImmutable', required: false, immutable: true)]
    public ?CarbonImmutable $createdAt;

    // Enum - with explicit class specification
    #[Property('UserStatus', enumClass: 'Grazulex\\Arc\\Examples\\UserStatus')]
    public ?UserStatus $status;

    // Nested DTO - with explicit class specification
    #[Property('ProfileDTO', dtoClass: 'Grazulex\\Arc\\Examples\\ProfileDTO')]
    public ?ProfileDTO $profile;

    // Collection of DTOs - auto-detected from array<> notation
    #[Property('array<AddressDTO>')]
    public array $addresses;

    // Alternative collection syntax - explicit collection flag
    #[Property('OrderDTO', collection: true, dtoClass: 'Grazulex\\Arc\\Examples\\OrderDTO')]
    public array $orders;

    // Manual cast override (for custom casters)
    #[Property('string', cast: 'custom_serialized', default: null)]
    public mixed $metadata;

    // Advanced example - nullable collection with custom format
    #[Property('array<PaymentDTO>', required: false, default: [])]
    public array $payments;
}
