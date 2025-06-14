<?php

namespace Grazulex\Arc;

use Grazulex\Arc\Commands\MakeDtoCommand;
use Illuminate\Support\ServiceProvider;

class ArcServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MakeDtoCommand::class,
        ]);
    }

    public function boot(): void {}
}
