<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Grazulex\Arc\ArcServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Illuminate\Validation\ValidationServiceProvider::class,
            ArcServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup the application environment for testing
        // Configure config for validation
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.cipher', 'AES-256-CBC');
    }
}

