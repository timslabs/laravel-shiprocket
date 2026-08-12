<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;
use Tims\LaravelShiprocket\Http\RetryMiddleware;
use Tims\LaravelShiprocket\Support\AccessTokenService;
use Tims\Shiprocket\Api\AccountApi;
use Tims\Shiprocket\Api\AuthApi;
use Tims\Shiprocket\Api\ChannelsApi;
use Tims\Shiprocket\Api\CouriersApi;
use Tims\Shiprocket\Api\ImportsApi;
use Tims\Shiprocket\Api\InternationalApi;
use Tims\Shiprocket\Api\InventoryApi;
use Tims\Shiprocket\Api\ListingsApi;
use Tims\Shiprocket\Api\NdrApi;
use Tims\Shiprocket\Api\OrdersApi;
use Tims\Shiprocket\Api\PickupApi;
use Tims\Shiprocket\Api\ProductsApi;
use Tims\Shiprocket\Api\ShipmentsApi;
use Tims\Shiprocket\Api\WarehouseApi;
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
        private readonly ?string $credential = null,
    ) {}

    /**
     * Use a named credential pair from config('shiprocket.credentials').
     */
    public function withCredential(string $name): self
    {
        $this->resolveCredentials($name);

        return new self($this->config, $name);
    }

    /**
     * Active credential key (default_credentials or withCredential override).
     */
    public function credential(): string
    {
        return $this->credential ?? (string) ($this->config['default_credentials'] ?? 'default');
    }

    /**
     * Build a configured ShiprocketClient (token + ApiClient).
     *
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
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
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function apiClient(
        ?ClientInterface $httpClient = null,
        array $options = [],
    ): ApiClient {
        $creds = $this->resolveCredentials(
            isset($options['credential']) ? (string) $options['credential'] : null,
        );

        $token = $options['token'] ?? $this->accessTokenService()->get(
            email: (string) ($options['email'] ?? $creds['email']),
            password: (string) ($options['password'] ?? $creds['password']),
            cacheId: $creds['name'],
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
     * Resolve (and cache) the JWT for the active credential.
     *
     * @param  array{email?: string, password?: string, credential?: string}  $options
     */
    public function getToken(array $options = []): string
    {
        $creds = $this->resolveCredentials(
            isset($options['credential']) ? (string) $options['credential'] : null,
        );

        return $this->accessTokenService()->get(
            email: (string) ($options['email'] ?? $creds['email']),
            password: (string) ($options['password'] ?? $creds['password']),
            cacheId: $creds['name'],
        );
    }

    /**
     * Instantiate an SDK API class with a shared ApiClient.
     *
     * @template T of object
     *
     * @param  class-string<T>  $apiClass
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
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
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function auth(?ClientInterface $httpClient = null, array $options = []): AuthApi
    {
        return $this->client($httpClient, $options)->auth();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function orders(?ClientInterface $httpClient = null, array $options = []): OrdersApi
    {
        return $this->client($httpClient, $options)->orders();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function couriers(?ClientInterface $httpClient = null, array $options = []): CouriersApi
    {
        return $this->client($httpClient, $options)->couriers();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function shipments(?ClientInterface $httpClient = null, array $options = []): ShipmentsApi
    {
        return $this->client($httpClient, $options)->shipments();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function pickup(?ClientInterface $httpClient = null, array $options = []): PickupApi
    {
        return $this->client($httpClient, $options)->pickup();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function products(?ClientInterface $httpClient = null, array $options = []): ProductsApi
    {
        return $this->client($httpClient, $options)->products();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function inventory(?ClientInterface $httpClient = null, array $options = []): InventoryApi
    {
        return $this->client($httpClient, $options)->inventory();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function listings(?ClientInterface $httpClient = null, array $options = []): ListingsApi
    {
        return $this->client($httpClient, $options)->listings();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function channels(?ClientInterface $httpClient = null, array $options = []): ChannelsApi
    {
        return $this->client($httpClient, $options)->channels();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function account(?ClientInterface $httpClient = null, array $options = []): AccountApi
    {
        return $this->client($httpClient, $options)->account();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function ndr(?ClientInterface $httpClient = null, array $options = []): NdrApi
    {
        return $this->client($httpClient, $options)->ndr();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function imports(?ClientInterface $httpClient = null, array $options = []): ImportsApi
    {
        return $this->client($httpClient, $options)->imports();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function international(?ClientInterface $httpClient = null, array $options = []): InternationalApi
    {
        return $this->client($httpClient, $options)->international();
    }

    /**
     * @param  array{retry?: bool, email?: string, password?: string, token?: string, credential?: string}  $options
     */
    public function warehouse(?ClientInterface $httpClient = null, array $options = []): WarehouseApi
    {
        return $this->client($httpClient, $options)->warehouse();
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

    public function forgetToken(?string $email = null, ?string $credential = null): void
    {
        $creds = $this->resolveCredentials($credential);

        $this->accessTokenService()->forget(
            $email ?? $creds['email'],
            $creds['name'],
        );
    }

    /**
     * @return array{email: string, password: string, name: string}
     */
    public function resolveCredentials(?string $name = null): array
    {
        $name ??= $this->credential();
        $credentials = $this->config['credentials'] ?? [];

        if (is_array($credentials) && isset($credentials[$name]) && is_array($credentials[$name])) {
            $email = (string) ($credentials[$name]['email'] ?? '');
            $password = (string) ($credentials[$name]['password'] ?? '');

            if ($email !== '' || $password !== '') {
                return [
                    'email' => $email,
                    'password' => $password,
                    'name' => $name,
                ];
            }
        }

        // Backward compatible top-level email/password (and empty credentials.default).
        if ($name === 'default' || $name === (string) ($this->config['default_credentials'] ?? 'default')) {
            $email = (string) ($this->config['email'] ?? '');
            $password = (string) ($this->config['password'] ?? '');

            if ($email !== '' || $password !== '') {
                return [
                    'email' => $email,
                    'password' => $password,
                    'name' => $name,
                ];
            }
        }

        if (is_array($credentials) && array_key_exists($name, $credentials)) {
            return [
                'email' => '',
                'password' => '',
                'name' => $name,
            ];
        }

        throw new InvalidArgumentException("Shiprocket credential [{$name}] is not configured.");
    }
}
