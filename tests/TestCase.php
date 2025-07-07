<?php

declare(strict_types=1);

namespace Tests;

use Grazulex\LaravelArc\LaravelArcServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected string $fakeAppPath;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->fakeAppPath);
        parent::tearDown();
    }

    final public function debugToFile(string $content, string $context = ''): void
    {
        $file = base_path('dto_test.log');
        $tag = $context ? "=== $context ===\n" : '';
        File::append($file, $tag.$content."\n");
    }

    protected function getEnvironmentSetUp($app): void
    {
        $token = getenv('TEST_TOKEN') ?: (string) Str::uuid();
        $this->fakeAppPath = sys_get_temp_dir()."/fake-app-{$token}";
        File::ensureDirectoryExists($this->fakeAppPath);

        $app->useAppPath($this->fakeAppPath);

        // ✅ Corrige le base_path
        $app->bind('path.base', fn () => dirname(__DIR__));

        // ✅ Corrige les chemins config
        // $app['config']->set('dto.definitions_path', realpath(__DIR__.'/../stubs/dto_definitions'));
        // $app['config']->set('dto.output_path', realpath(__DIR__.'/../stubs/dto_output'));
        $app['config']->set('dto.definitions_path', base_path('tests/stubs/dto_definitions'));
        $app['config']->set('dto.output_path', base_path('tests/stubs/dto_output'));
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelArcServiceProvider::class,
        ];
    }
}
