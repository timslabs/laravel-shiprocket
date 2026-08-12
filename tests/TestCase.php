<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tims\LaravelShiprocket\Facades\Shiprocket;
use Tims\LaravelShiprocket\ShiprocketServiceProvider;
use Tims\LaravelShiprocket\Support\LaravelTokenCache;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ShiprocketServiceProvider::class,
        ];
    }

    /**
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Shiprocket' => Shiprocket::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('shiprocket.email', 'api@example.com');
        $app['config']->set('shiprocket.password', 'secret');
        $app['config']->set('shiprocket.token_cache.enabled', true);
        $app['config']->set('shiprocket.retry.enabled', true);
        $app['config']->set('cache.default', 'array');
    }

    protected function seedAccessToken(string $email = 'api@example.com', string $token = 'test-access-token'): void
    {
        (new LaravelTokenCache('default'))->set(
            strtolower(trim($email)),
            $token,
            3600,
        );
    }
}
