<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Tests;

use InvalidArgumentException;
use Tims\LaravelShiprocket\Facades\Shiprocket;
use Tims\LaravelShiprocket\ShiprocketManager;
use Tims\LaravelShiprocket\Support\LaravelTokenCache;
use Tims\Shiprocket\Api\CouriersApi;
use Tims\Shiprocket\Api\OrdersApi;
use Tims\Shiprocket\Api\WarehouseApi;

class ManagerCredentialsTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('shiprocket.credentials', [
            'default' => [
                'email' => 'api@example.com',
                'password' => 'secret',
            ],
            'second' => [
                'email' => 'second@example.com',
                'password' => 'second-secret',
            ],
        ]);
        $app['config']->set('shiprocket.default_credentials', 'default');
    }

    public function test_resolves_named_credentials(): void
    {
        $manager = $this->app->make(ShiprocketManager::class);

        $this->assertSame([
            'email' => 'api@example.com',
            'password' => 'secret',
            'name' => 'default',
        ], $manager->resolveCredentials());

        $this->assertSame([
            'email' => 'second@example.com',
            'password' => 'second-secret',
            'name' => 'second',
        ], $manager->withCredential('second')->resolveCredentials());
    }

    public function test_with_credential_returns_new_manager(): void
    {
        $manager = $this->app->make(ShiprocketManager::class);
        $scoped = $manager->withCredential('second');

        $this->assertNotSame($manager, $scoped);
        $this->assertSame('default', $manager->credential());
        $this->assertSame('second', $scoped->credential());
    }

    public function test_unknown_credential_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shiprocket credential [missing] is not configured.');

        $this->app->make(ShiprocketManager::class)->withCredential('missing');
    }

    public function test_forget_token_uses_credential_cache_namespace(): void
    {
        (new LaravelTokenCache('second'))->set('second@example.com', 'second-jwt', 3600);

        Shiprocket::withCredential('second')->forgetToken();

        $this->assertNull((new LaravelTokenCache('second'))->get('second@example.com'));
    }

    public function test_facade_resource_shortcuts(): void
    {
        $this->seedAccessToken();

        $this->assertInstanceOf(OrdersApi::class, Shiprocket::orders());
        $this->assertInstanceOf(CouriersApi::class, Shiprocket::couriers());
        $this->assertInstanceOf(WarehouseApi::class, Shiprocket::warehouse());
        $this->assertSame('test-access-token', Shiprocket::orders()->getClient()->getAccessToken());
    }

    public function test_get_token_reads_cache(): void
    {
        $this->seedAccessToken(token: 'cached-jwt');

        $this->assertSame('cached-jwt', Shiprocket::getToken());
    }

    public function test_legacy_top_level_credentials_still_work(): void
    {
        config([
            'shiprocket.credentials' => [],
            'shiprocket.email' => 'legacy@example.com',
            'shiprocket.password' => 'legacy-secret',
        ]);

        $manager = new ShiprocketManager(config('shiprocket'));

        $this->assertSame([
            'email' => 'legacy@example.com',
            'password' => 'legacy-secret',
            'name' => 'default',
        ], $manager->resolveCredentials());
    }
}
