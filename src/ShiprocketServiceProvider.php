<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket;

use Illuminate\Support\ServiceProvider;
use Tims\Shiprocket\ApiClient;
use Tims\Shiprocket\ShiprocketClient;

class ShiprocketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shiprocket.php', 'shiprocket');

        $this->app->singleton(ShiprocketManager::class, function ($app) {
            return new ShiprocketManager(
                config: $app['config']->get('shiprocket', []),
            );
        });

        $this->app->alias(ShiprocketManager::class, 'shiprocket');

        $this->app->bind(ShiprocketClient::class, function ($app) {
            return $app->make(ShiprocketManager::class)->client();
        });

        $this->app->bind(ApiClient::class, function ($app) {
            return $app->make(ShiprocketManager::class)->apiClient();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/shiprocket.php' => config_path('shiprocket.php'),
            ], 'shiprocket-config');
        }
    }

    /**
     * @return list<string>
     */
    public function provides(): array
    {
        return [
            ShiprocketManager::class,
            'shiprocket',
            ShiprocketClient::class,
            ApiClient::class,
        ];
    }
}
