<?php

declare(strict_types=1);

namespace Grazulex\LaravelArc;

use Grazulex\LaravelArc\Console\Commands\DtoDefinitionInitCommand;
use Grazulex\LaravelArc\Console\Commands\DtoDefinitionListCommand;
use Illuminate\Support\ServiceProvider;

final class LaravelArcServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/Config/dto.php' => config_path('dto.php'),
        ], 'dto-config');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/Config/dto.php', 'dto');
        $this->commands([
            DtoDefinitionInitCommand::class,
            DtoDefinitionListCommand::class,
        ]);
    }
}
