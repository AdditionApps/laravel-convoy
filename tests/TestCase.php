<?php

namespace AdditionApps\Convoy\Tests;

use AdditionApps\Convoy\ConvoyServiceProvider;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $basePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->basePath = realpath(__DIR__.'/..');
        $this->setupAdditionalMigrations();
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    protected function setupAdditionalMigrations()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/../tests/Support/Migrations'));
    }

    protected function getPackageProviders($app)
    {
        return [
            ConvoyServiceProvider::class,
            ConsoleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [
            realpath(__DIR__.'/../tests/Support/Views'),
        ]);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
