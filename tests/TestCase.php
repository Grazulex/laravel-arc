<?php

namespace Tests;

use Grazulex\Arc\ArcServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ValidationServiceProvider::class,
            ArcServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup the application environment for testing
        // Configure config for validation
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $app['config']->set('app.cipher', 'AES-256-CBC');
    }
}
