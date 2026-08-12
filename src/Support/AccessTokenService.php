<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Support;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Tims\Shiprocket\AccessTokenGenerator;
use Tims\Shiprocket\ApiException;
use Tims\Shiprocket\Constants;

/**
 * Resolve Shiprocket JWTs, caching them in Laravel when enabled.
 */
class AccessTokenService
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config = [],
        private readonly ?ClientInterface $httpClient = null,
    ) {}

    /**
     * Resolve an access token for the given API user.
     *
     * @throws ApiException
     */
    public function get(string $email, string $password, string $cacheId = 'default'): string
    {
        if ($email === '' || $password === '') {
            throw new InvalidArgumentException(
                'Shiprocket requires API credentials. Set SHIPROCKET_EMAIL / SHIPROCKET_PASSWORD or config shiprocket.credentials.'
            );
        }

        $cacheEnabled = (bool) ($this->config['token_cache']['enabled'] ?? true);
        $cache = new LaravelTokenCache($cacheId);

        if ($cacheEnabled) {
            $cached = $cache->get($this->cacheKey($email));
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $baseUrl = (string) ($this->config['base_url'] ?? Constants::BASE_URL);
        $generator = new AccessTokenGenerator(
            $this->httpClient ?? new Client([
                'http_errors' => false,
                'timeout' => (float) ($this->config['http']['timeout'] ?? 30),
            ]),
            $baseUrl,
        );

        $token = $generator->credentials($email, $password)->getAccessToken();
        $ttl = $this->resolveTtl();

        if ($cacheEnabled) {
            $cache->set($this->cacheKey($email), $token, $ttl);
        }

        return $token;
    }

    public function forget(string $email, string $cacheId = 'default'): void
    {
        (new LaravelTokenCache($cacheId))->remove($this->cacheKey($email));
    }

    private function resolveTtl(): int
    {
        $buffer = max(0, (int) ($this->config['token_cache']['expiration_buffer_seconds'] ?? 3600));
        $default = max(0, (int) ($this->config['token_cache']['default_ttl_seconds'] ?? Constants::TOKEN_TTL_SECONDS));

        return max(0, $default - $buffer);
    }

    private function cacheKey(string $email): string
    {
        return strtolower(trim($email));
    }
}
