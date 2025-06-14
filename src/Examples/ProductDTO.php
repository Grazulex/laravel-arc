<?php

namespace Grazulex\Arc\Examples;

use Carbon\Carbon;
use Grazulex\Arc\Attributes\Property;
use Grazulex\Arc\LaravelArcDTO;

/**
 * Example DTO created with:
 * php artisan make:dto Product --model=Product
 *
 * This shows what would be generated for a typical Product model
 */
class ProductDTO extends LaravelArcDTO
{
    #[Property(type: 'int', required: false)]
    public ?int $id;

    #[Property(type: 'string', required: false)]
    public ?string $name;

    #[Property(type: 'string', required: false)]
    public ?string $description;

    #[Property(type: 'float', required: false)]
    public ?float $price;

    #[Property(type: 'int', required: false)]
    public ?int $quantity;

    #[Property(type: 'string', required: false)]
    public ?string $sku;

    #[Property(type: 'bool', required: false, default: false)]
    public ?bool $is_active;

    #[Property(type: 'string', required: false, validation: 'url')]
    public ?string $image_url;

    #[Property('Carbon', required: false)]
    public ?Carbon $created_at;

    #[Property('Carbon', required: false)]
    public ?Carbon $updated_at;
}
