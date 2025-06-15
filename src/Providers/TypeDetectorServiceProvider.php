<?php

declare(strict_types=1);

namespace Grazulex\Arc\Providers;

use Grazulex\Arc\Services\Commands\Generation\TypeDetectors\CastTypeDetector;
use Grazulex\Arc\Services\Commands\Generation\TypeDetectors\DatabaseTypeDetector;
use Grazulex\Arc\Services\Commands\Generation\TypeDetectors\PatternTypeDetector;
use Grazulex\Arc\Services\Commands\Generation\TypeDetectors\TypeDetectorInterface;
use Illuminate\Support\ServiceProvider;

final class TypeDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register all type detectors as singletons
        $this->app->singleton(CastTypeDetector::class);
        $this->app->singleton(DatabaseTypeDetector::class);
        $this->app->singleton(PatternTypeDetector::class);

        // Bind the interface to specific implementations based on context
        $this->app->bind(TypeDetectorInterface::class . ':cast', CastTypeDetector::class);
        $this->app->bind(TypeDetectorInterface::class . ':database', DatabaseTypeDetector::class);
        $this->app->bind(TypeDetectorInterface::class . ':pattern', PatternTypeDetector::class);

        // Default to PatternTypeDetector when no specific context is given
        $this->app->bind(TypeDetectorInterface::class, PatternTypeDetector::class);

        // Tag the type detectors for potential use elsewhere
        $this->app->tag([CastTypeDetector::class, DatabaseTypeDetector::class, PatternTypeDetector::class], 'type_detectors');
    }
}
