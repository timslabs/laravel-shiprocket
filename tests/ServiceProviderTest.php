<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Tests;

use Tims\LaravelShiprocket\Facades\Shiprocket;
use Tims\LaravelShiprocket\ShiprocketManager;
use Tims\LaravelShiprocket\ShiprocketServiceProvider;
use Tims\Shiprocket\ApiClient;
use Tims\Shiprocket\ShiprocketClient;

class ServiceProviderTest extends TestCase
{
    public function test_registers_manager_as_singleton(): void
    {
        $a = $this->app->make(ShiprocketManager::class);
        $b = $this->app->make(ShiprocketManager::class);

        $this->assertSame($a, $b);
        $this->assertSame($a, $this->app->make('shiprocket'));
    }

    public function test_binds_client_from_cached_token(): void
    {
        $this->seedAccessToken();

        $this->assertTrue($this->app->bound(ShiprocketClient::class));
        $this->assertInstanceOf(ShiprocketClient::class, $this->app->make(ShiprocketClient::class));
        $this->assertInstanceOf(ApiClient::class, $this->app->make(ApiClient::class));
        $this->assertSame('test-access-token', $this->app->make(ApiClient::class)->getAccessToken());
    }

    public function test_facade_resolves_to_manager(): void
    {
        $this->assertInstanceOf(ShiprocketManager::class, Shiprocket::getFacadeRoot());
    }

    public function test_merges_package_config(): void
    {
        $this->assertSame('api@example.com', config('shiprocket.email'));
        $this->assertTrue(config('shiprocket.token_cache.enabled'));
        $this->assertTrue(config('shiprocket.retry.enabled'));
        $this->assertSame(864000, config('shiprocket.token_cache.default_ttl_seconds'));
    }

    public function test_publishes_config(): void
    {
        $this->artisan('vendor:publish', [
            '--provider' => ShiprocketServiceProvider::class,
            '--tag' => 'shiprocket-config',
        ])->assertSuccessful();

        $this->assertFileExists(config_path('shiprocket.php'));
    }

    public function test_provider_declares_provides(): void
    {
        $provider = new ShiprocketServiceProvider($this->app);

        $this->assertSame([
            ShiprocketManager::class,
            'shiprocket',
            ShiprocketClient::class,
            ApiClient::class,
        ], $provider->provides());
    }
}
