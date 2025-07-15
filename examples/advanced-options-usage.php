<?php

declare(strict_types=1);
/**
 * Advanced Options Usage Examples
 *
 * This file demonstrates how to use the new advanced options available in Laravel Arc.
 * Generate the AdvancedProductDTO first: php artisan dto:generate advanced-options.yaml
 */

use App\DTO\Advanced\AdvancedProductDTO;
use App\Enums\ProductCategory;

// =============================================================================
// UUID Option Examples
// =============================================================================

// Generate a new UUID
$uuid = AdvancedProductDTO::generateUuid();
echo "Generated UUID: {$uuid}\n";

// Create DTO with generated UUID
$product = AdvancedProductDTO::withGeneratedUuid([
    'name' => 'Test Product',
    'description' => 'A test product',
    'price' => 29.99,
    'category' => ProductCategory::ELECTRONICS,
    'is_active' => true,
]);

echo "Product with UUID: {$product->id}\n";

// =============================================================================
// Versioning Option Examples
// =============================================================================

// Create next version
$nextVersion = $product->nextVersion();
echo "Current version: {$product->version}, Next version: {$nextVersion->version}\n";

// Check if one version is newer
$isNewer = $nextVersion->isNewerThan($product);
echo 'Next version is newer: '.($isNewer ? 'Yes' : 'No')."\n";

// Get version information
$versionInfo = $product->getVersionInfo();
echo 'Version info: '.json_encode($versionInfo)."\n";

// =============================================================================
// Taggable Option Examples
// =============================================================================

// Add tags
$taggedProduct = $product->addTag('featured');
$taggedProduct = $taggedProduct->addTag('bestseller');
$taggedProduct = $taggedProduct->addTag('new');

echo 'Tags: '.implode(', ', $taggedProduct->getTags())."\n";

// Check if has tag
$hasFeatured = $taggedProduct->hasTag('featured');
echo "Has 'featured' tag: ".($hasFeatured ? 'Yes' : 'No')."\n";

// Remove tag
$untaggedProduct = $taggedProduct->removeTag('new');
echo 'Tags after removal: '.implode(', ', $untaggedProduct->getTags())."\n";

// Filter DTO by tag (static method)
$products = [$product, $taggedProduct, $untaggedProduct];
$featuredProducts = AdvancedProductDTO::withTag($products, 'featured');
echo 'Featured products count: '.count($featuredProducts)."\n";

// =============================================================================
// Immutable Option Examples
// =============================================================================

// Create new instance with changes
$modifiedProduct = $product->with([
    'name' => 'Modified Product Name',
    'price' => 39.99,
]);

echo "Original name: {$product->name}, Modified name: {$modifiedProduct->name}\n";

// Copy DTO
$copiedProduct = $product->copy();
echo 'Copied product equals original: '.($copiedProduct->equals($product) ? 'Yes' : 'No')."\n";

// Get hash for caching or comparison
$hash = $product->hash();
echo "Product hash: {$hash}\n";

// =============================================================================
// Auditable Option Examples
// =============================================================================

$userId = '550e8400-e29b-41d4-a716-446655440000';

// Set creator
$auditedProduct = $product->setCreator($userId);
echo "Created by: {$auditedProduct->created_by}\n";

// Set updater
$updatedProduct = $auditedProduct->setUpdater($userId);
echo "Updated by: {$updatedProduct->updated_by}\n";

// Create audit trail
$auditTrail = $updatedProduct->createAuditTrail('updated', $userId);
echo 'Audit trail: '.json_encode($auditTrail)."\n";

// Get audit info
$auditInfo = $updatedProduct->getAuditInfo();
echo 'Audit info: '.json_encode($auditInfo)."\n";

// =============================================================================
// Cacheable Option Examples
// =============================================================================

// Get cache key
$cacheKey = $product->getCacheKey();
echo "Cache key: {$cacheKey}\n";

// Cache the DTO
$product->cache(3600); // Cache for 1 hour
echo "Product cached successfully\n";

// Check if cached
$isCached = $product->isCached();
echo 'Is cached: '.($isCached ? 'Yes' : 'No')."\n";

// Get from cache
$cachedProduct = AdvancedProductDTO::fromCache($cacheKey);
echo 'Retrieved from cache: '.($cachedProduct ? 'Yes' : 'No')."\n";

// Get cache metadata
$cacheMetadata = $product->getCacheMetadata();
echo 'Cache metadata: '.json_encode($cacheMetadata)."\n";

// Clear cache
$cleared = $product->clearCache();
echo 'Cache cleared: '.($cleared ? 'Yes' : 'No')."\n";

// =============================================================================
// Sluggable Option Examples
// =============================================================================

// Generate slug from name field
$sluggedProduct = $product->generateSlug();
echo "Generated slug: {$sluggedProduct->slug}\n";

// Update slug when source field changes
$renamedProduct = $product->with(['name' => 'New Product Name']);
$updatedSlugProduct = $renamedProduct->updateSlug();
echo "Updated slug: {$updatedSlugProduct->slug}\n";

// Get slug (auto-generates if not set)
$slug = $product->getSlug();
echo "Product slug: {$slug}\n";

// Check if slug is unique (basic check)
$isUnique = $sluggedProduct->hasUniqueSlug();
echo 'Slug is unique: '.($isUnique ? 'Yes' : 'No')."\n";

// =============================================================================
// Combined Usage Examples
// =============================================================================

// Create a complete product with all features
$completeProduct = AdvancedProductDTO::withGeneratedUuid([
    'name' => 'Complete Product',
    'description' => 'A product using all advanced features',
    'price' => 99.99,
    'category' => ProductCategory::ELECTRONICS,
    'is_active' => true,
])
    ->generateSlug()
    ->addTag('premium')
    ->addTag('featured')
    ->setCreator($userId)
    ->cache(7200); // Cache for 2 hours

echo "\nComplete product created with:\n";
echo "- UUID: {$completeProduct->id}\n";
echo "- Version: {$completeProduct->version}\n";
echo "- Slug: {$completeProduct->slug}\n";
echo '- Tags: '.implode(', ', $completeProduct->getTags())."\n";
echo "- Created by: {$completeProduct->created_by}\n";
echo "- Cache key: {$completeProduct->getCacheKey()}\n";
echo '- Is cached: '.($completeProduct->isCached() ? 'Yes' : 'No')."\n";

// =============================================================================
// Export with New Options
// =============================================================================

// Export the complete product in different formats
echo "\n=== Export Examples ===\n";

// JSON export
echo 'JSON: '.$completeProduct->toJson()."\n";

// Markdown export
echo 'Markdown: '.$completeProduct->toMarkdownTable()."\n";

// CSV export
echo 'CSV: '.$completeProduct->toCsv()."\n";

// Collection export with multiple products
$products = [$product, $completeProduct];
$collection = AdvancedProductDTO::collection($products);

echo 'Collection JSON: '.$collection->toJson()."\n";
echo 'Collection Markdown: '.AdvancedProductDTO::collectionToMarkdownTable($products)."\n";

echo "\n=== Advanced Options Demo Complete ===\n";
