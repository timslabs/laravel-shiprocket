<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Facades;

use Illuminate\Support\Facades\Facade;
use Tims\LaravelShiprocket\ShiprocketManager;

/**
 * @method static \Tims\Shiprocket\ShiprocketClient client(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\ApiClient apiClient(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static object make(string $apiClass, ?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \GuzzleHttp\Client createHttpClient(array $options = [])
 * @method static \Tims\LaravelShiprocket\Support\AccessTokenService accessTokenService()
 * @method static void forgetToken(?string $email = null)
 *
 * @see ShiprocketManager
 */
class Shiprocket extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ShiprocketManager::class;
    }
}
