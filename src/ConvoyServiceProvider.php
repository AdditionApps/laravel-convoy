<?php

namespace AdditionApps\Convoy;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use AdditionApps\Convoy\Contracts\ConvoyRepositoryContract;
use AdditionApps\Convoy\Contracts\ManifestContract;
use AdditionApps\Convoy\Contracts\MonitorContract;
use AdditionApps\Convoy\Exceptions\ConvoyException;
use AdditionApps\Convoy\Support\Convoy;
use AdditionApps\Convoy\Support\Manifest;
use AdditionApps\Convoy\Support\Monitor;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class ConvoyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfig();
        $this->mergeConfig();
        $this->registerMigrations();
        $this->registerViews();
        $this->registerQueueEvents();

        $this->app->bind(ConvoyRepositoryContract::class, $this->getRepository());
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/convoy.php' => config_path('convoy.php'),
        ], 'config');
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/convoy.php', 'convoy');
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel_convoy');
    }

    protected function registerQueueEvents(): void
    {
        Queue::after(function (JobProcessed $event) {
            $this->app->make(Monitor::class)->reportComplete($event);
        });

        Queue::failing(function (JobFailed $event) {
            $this->app->make(Monitor::class)->reportFailed($event);
        });
    }

    /**
     * @throws \AdditionApps\Convoy\Exceptions\ConvoyException
     */
    protected function getRepository(): string
    {
        $driver = ucfirst(config('convoy.driver')).'ConvoyRepository';

        $repository = "AdditionApps\\Convoy\\Repositories\\{$driver}";

        if (! class_exists($repository)) {
            throw ConvoyException::incorrectRepositoryDriver($driver);
        }

        return $repository;
    }

    public function register(): void
    {
        $this->app->bind(ConvoyContract::class, Convoy::class);
        $this->app->bind(ManifestContract::class, Manifest::class);
        $this->app->bind(MonitorContract::class, Monitor::class);
    }
}
