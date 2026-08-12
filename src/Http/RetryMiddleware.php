<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Http;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Guzzle retries for transient Shiprocket API failures (429 / 5xx / connection errors).
 */
class RetryMiddleware
{
    /**
     * @param  array{enabled?: bool, max_attempts?: int, base_delay_ms?: int, status_codes?: list<int>}  $config
     */
    public static function create(array $config = []): callable
    {
        $maxAttempts = max(1, (int) ($config['max_attempts'] ?? 3));
        $baseDelayMs = max(0, (int) ($config['base_delay_ms'] ?? 500));
        $statusCodes = $config['status_codes'] ?? [429, 500, 502, 503, 504];

        return Middleware::retry(
            static function (
                int $retries,
                RequestInterface $request,
                ?ResponseInterface $response = null,
                ?\Throwable $exception = null,
            ) use ($maxAttempts, $statusCodes): bool {
                if ($retries + 1 >= $maxAttempts) {
                    return false;
                }

                if ($exception instanceof ConnectException) {
                    return true;
                }

                if ($response !== null && in_array($response->getStatusCode(), $statusCodes, true)) {
                    return true;
                }

                return false;
            },
            static function (
                int $retries,
                ?ResponseInterface $response = null,
            ) use ($baseDelayMs): int {
                if ($response !== null && $response->hasHeader('Retry-After')) {
                    $retryAfter = $response->getHeaderLine('Retry-After');

                    if (is_numeric($retryAfter)) {
                        return max(0, (int) $retryAfter) * 1000;
                    }
                }

                return (int) ($baseDelayMs * (2 ** max(0, $retries - 1)));
            },
        );
    }
}
