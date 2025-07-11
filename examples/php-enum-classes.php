<?php

declare(strict_types=1);

/**
 * Example PHP enum classes for Laravel Arc
 * 
 * These enums demonstrate the different types of enum classes
 * that can be used with Laravel Arc's enum field generation.
 */

namespace App\Enums;

/**
 * String enum example
 */
enum Priority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
}

/**
 * String enum for categories
 */
enum Category: string
{
    case GENERAL = 'general';
    case TECHNOLOGY = 'technology';
    case BUSINESS = 'business';
    case SPORTS = 'sports';
    case ENTERTAINMENT = 'entertainment';
}

/**
 * String enum for visibility
 */
enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case PROTECTED = 'protected';
}

/**
 * Int enum example
 */
enum Level: int
{
    case BEGINNER = 1;
    case INTERMEDIATE = 2;
    case ADVANCED = 3;
}

/**
 * String enum for badges
 */
enum Badge: string
{
    case GOLD = 'gold';
    case SILVER = 'silver';
    case BRONZE = 'bronze';
}

/**
 * Order status enum
 */
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}

/**
 * Currency enum
 */
enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case CAD = 'CAD';
}

/**
 * Product status enum
 */
enum ProductStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}

/**
 * Pure enum (no backing type)
 */
enum Color
{
    case RED;
    case GREEN;
    case BLUE;
}