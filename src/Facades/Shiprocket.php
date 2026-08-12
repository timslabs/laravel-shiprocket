<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Facades;

use Illuminate\Support\Facades\Facade;
use Tims\LaravelShiprocket\ShiprocketManager;

/**
 * @method static ShiprocketManager withCredential(string $name)
 * @method static string credential()
 * @method static \Tims\Shiprocket\ShiprocketClient client(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\ApiClient apiClient(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static string getToken(array $options = [])
 * @method static object make(string $apiClass, ?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\AuthApi auth(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\OrdersApi orders(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\CouriersApi couriers(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\ShipmentsApi shipments(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\PickupApi pickup(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\ProductsApi products(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\InventoryApi inventory(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\ListingsApi listings(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\ChannelsApi channels(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\AccountApi account(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\NdrApi ndr(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\ImportsApi imports(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\InternationalApi international(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \Tims\Shiprocket\Api\WarehouseApi warehouse(?\GuzzleHttp\ClientInterface $httpClient = null, array $options = [])
 * @method static \GuzzleHttp\Client createHttpClient(array $options = [])
 * @method static \Tims\LaravelShiprocket\Support\AccessTokenService accessTokenService()
 * @method static void forgetToken(?string $email = null, ?string $credential = null)
 * @method static array{email: string, password: string, name: string} resolveCredentials(?string $name = null)
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
