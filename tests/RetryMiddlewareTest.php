<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Tests;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tims\LaravelShiprocket\Http\RetryMiddleware;

class RetryMiddlewareTest extends TestCase
{
    public function test_retries_on_429_and_5xx(): void
    {
        $middleware = RetryMiddleware::create([
            'max_attempts' => 3,
            'base_delay_ms' => 100,
            'status_codes' => [429, 500, 502, 503, 504],
        ]);

        $attempts = 0;
        $handler = function ($request, $options) use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                return Create::promiseFor(new Response(429));
            }

            return Create::promiseFor(new Response(200));
        };

        $retryHandler = $middleware($handler);
        $response = $retryHandler(new Request('GET', 'https://example.com'), [])->wait();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(3, $attempts);
    }

    public function test_retries_on_connect_exception(): void
    {
        $attempts = 0;
        $middleware = RetryMiddleware::create([
            'max_attempts' => 2,
            'base_delay_ms' => 1,
        ]);

        $handler = function ($request, $options) use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                return Create::rejectionFor(
                    new ConnectException('boom', $request),
                );
            }

            return Create::promiseFor(new Response(200));
        };

        $response = $middleware($handler)(new Request('GET', 'https://example.com'), [])->wait();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $attempts);
    }

    public function test_does_not_retry_beyond_max_attempts(): void
    {
        $attempts = 0;
        $middleware = RetryMiddleware::create([
            'max_attempts' => 2,
            'base_delay_ms' => 1,
        ]);

        $handler = function ($request, $options) use (&$attempts) {
            $attempts++;

            return Create::promiseFor(new Response(503));
        };

        $response = $middleware($handler)(new Request('GET', 'https://example.com'), [])->wait();

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(2, $attempts);
    }
}
