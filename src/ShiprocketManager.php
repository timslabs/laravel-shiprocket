<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use Tims\LaravelShiprocket\Http\RetryMiddleware;
use Tims\LaravelShiprocket\Support\AccessTokenService;
use Tims\Shiprocket\ApiClient;
use Tims\Shiprocket\Constants;
use Tims\Shiprocket\ShiprocketClient;

class ShiprocketManager
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config = [],
    ) {}

    /**
     * Build a configured ShiprocketClient (token + ApiClient).
     *
     * @param  array{retry?: bool, email?: string, password?: string, token?: string}  $options
     */
    public function client(
        ?ClientInterface $httpClient = null,
        array $options = [],
    ): ShiprocketClient {
        return new ShiprocketClient(
            $this->apiClient($httpClient, $options),
        );
    }

    /**
     * Build a configured ApiClient with a resolved access token.
     *
     * @param  array{retry?: bool, email?: string, password?: string, token?: string}  $options
     */
    public function apiClient(
        ?ClientInterface $httpClient = null,
        array $options = [],
    ): ApiClient {
        $token = $options['token'] ?? $this->accessTokenService()->get(
            email: (string) ($options['email'] ?? $this->config['email'] ?? ''),
            password: (string) ($options['password'] ?? $this->config['password'] ?? ''),
        );

        $baseUrl = rtrim((string) ($this->config['base_url'] ?? Constants::BASE_URL), '/');

        $client = new ApiClient(
            basePath: $baseUrl.Constants::API_PREFIX,
            httpClient: $httpClient ?? $this->createHttpClient($options),
            guzzleOptions: [
                'timeout' => (float) ($this->config['http']['timeout'] ?? 60),
            ],
        );

        $client->setAccessToken($token);

        if (! empty($this->config['debug'])) {
            $client->setDebugging(true);
        }

        return $client;
    }

    /**
     * Instantiate an SDK API class with a shared ApiClient.
     *
     * @template T of object
     *
     * @param  class-string<T>  $apiClass
     * @param  array{retry?: bool, email?: string, password?: string, token?: string}  $options
     * @return T
     */
    public function make(
        string $apiClass,
        ?ClientInterface $httpClient = null,
        array $options = [],
    ): object {
        if (! class_exists($apiClass)) {
            throw new InvalidArgumentException("API class [{$apiClass}] does not exist.");
        }

        return new $apiClass(
            $this->apiClient($httpClient, $options),
        );
    }

    /**
     * Build a Guzzle client with optional retry middleware.
     *
     * @param  array{retry?: bool}  $options
     */
    public function createHttpClient(array $options = []): Client
    {
        $retry = $options['retry'] ?? $this->config['retry']['enabled'] ?? true;

        if (! $retry) {
            return new Client([
                'http_errors' => false,
                'timeout' => (float) ($this->config['http']['timeout'] ?? 60),
            ]);
        }

        $stack = HandlerStack::create();
        $stack->push(RetryMiddleware::create($this->config['retry'] ?? []));

        return new Client([
            'handler' => $stack,
            'http_errors' => false,
            'timeout' => (float) ($this->config['http']['timeout'] ?? 60),
        ]);
    }

    public function accessTokenService(): AccessTokenService
    {
        return new AccessTokenService($this->config);
    }

    public function forgetToken(?string $email = null): void
    {
        $this->accessTokenService()->forget($email ?? (string) ($this->config['email'] ?? ''));
    }
}
