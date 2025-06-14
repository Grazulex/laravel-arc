<?php

namespace Grazulex\Arc;

use Grazulex\Arc\Commands\AnalyzeDtoCommand;
use Grazulex\Arc\Commands\MakeDtoCommand;
use Grazulex\Arc\Commands\ValidateDtoCommand;
use Illuminate\Support\ServiceProvider;

class ArcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MakeDtoCommand::class,
            AnalyzeDtoCommand::class,
            ValidateDtoCommand::class,
        ]);
    }

    public function boot(): void {}
}
