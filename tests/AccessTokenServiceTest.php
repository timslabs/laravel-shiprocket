<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tims\LaravelShiprocket\Facades\Shiprocket;
use Tims\LaravelShiprocket\Support\AccessTokenService;
use Tims\LaravelShiprocket\Support\LaravelTokenCache;
use Tims\Shiprocket\Api\OrdersApi;

class AccessTokenServiceTest extends TestCase
{
    public function test_get_uses_cache_when_present(): void
    {
        $this->seedAccessToken(token: 'cached-jwt');

        $token = (new AccessTokenService(config('shiprocket')))->get('api@example.com', 'secret');

        $this->assertSame('cached-jwt', $token);
    }

    public function test_get_logs_in_and_caches_token(): void
    {
        $history = [];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"token":"fresh-jwt"}'),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $service = new AccessTokenService(
            config('shiprocket'),
            new Client(['handler' => $stack, 'http_errors' => false]),
        );

        $token = $service->get('api@example.com', 'secret');

        $this->assertSame('fresh-jwt', $token);
        $this->assertCount(1, $history);
        $this->assertSame(
            'fresh-jwt',
            (new LaravelTokenCache('default'))->get('api@example.com'),
        );
    }

    public function test_forget_token_clears_cache(): void
    {
        $this->seedAccessToken(token: 'cached-jwt');

        Shiprocket::forgetToken();

        $this->assertNull((new LaravelTokenCache('default'))->get('api@example.com'));
    }

    public function test_manager_make_builds_orders_api(): void
    {
        $this->seedAccessToken();

        $api = Shiprocket::make(OrdersApi::class);

        $this->assertInstanceOf(OrdersApi::class, $api);
        $this->assertSame('test-access-token', $api->getClient()->getAccessToken());
    }
}
