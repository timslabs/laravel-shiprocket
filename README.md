# laravel-shiprocket

Laravel integration for the Shiprocket PHP SDK ([`tims/shiprocket-php-sdk`](https://packagist.org/packages/tims/shiprocket-php-sdk)).

## Why this package?

`tims/shiprocket-php-sdk` is the API client (PHP **8.0+**). This package adds the Laravel layer around it:

- Config and environment-based API user credentials
- JWT access-token caching via Laravel Cache (tokens last ~10 days)
- HTTP retries for 429 and transient 5xx responses
- Service-container bindings and a `Shiprocket` facade

You continue to call `Tims\Shiprocket\ShiprocketClient` / `Tims\Shiprocket\Api\...`; this package configures and supports them in Laravel.

## Requirements

- PHP 8.3+
- Laravel 10, 11, or 12
- `tims/shiprocket-php-sdk` ^1.0

## Installation

```bash
composer require tims/laravel-shiprocket
```

Publish the config:

```bash
php artisan vendor:publish --tag=shiprocket-config
```

## Configuration

Add these to your `.env`:

```env
SHIPROCKET_EMAIL=
SHIPROCKET_PASSWORD=

# Optional overrides
SHIPROCKET_BASE_URL=https://apiv2.shiprocket.in

# Token cache (enabled by default; JWTs last ~10 days)
SHIPROCKET_TOKEN_CACHE=true
SHIPROCKET_TOKEN_TTL=864000
SHIPROCKET_TOKEN_BUFFER=3600

# Retries for 429 / 5xx (enabled by default)
SHIPROCKET_RETRY_ENABLED=true
SHIPROCKET_RETRY_MAX_ATTEMPTS=3
SHIPROCKET_RETRY_BASE_DELAY_MS=500

SHIPROCKET_DEBUG=false
SHIPROCKET_HTTP_TIMEOUT=60
```

Create an API user in Shiprocket: **Settings → API → Configure → Create an API User**. Use that email/password (not your panel login).

## Usage

Type-hint `ShiprocketManager` or use the facade:

```php
use Tims\LaravelShiprocket\Facades\Shiprocket;
use Tims\LaravelShiprocket\ShiprocketManager;
use Tims\Shiprocket\Api\OrdersApi;

public function index(ShiprocketManager $shiprocket)
{
    $client = $shiprocket->client();

    $rates = $client->couriers()->serviceability([
        'pickup_postcode' => '110030',
        'delivery_postcode' => '122001',
        'weight' => 0.5,
        'cod' => 0,
    ]);

    return response()->json($rates);
}

// Or build an SDK API class directly:
$api = Shiprocket::make(OrdersApi::class);
$result = $api->list(['page' => 1]);
```

You can also resolve `Tims\Shiprocket\ShiprocketClient` and `Tims\Shiprocket\ApiClient` from the container.

SDK resource helpers available via `$shiprocket->client()`:

`auth`, `orders`, `couriers`, `shipments`, `pickup`, `products`, `inventory`, `listings`, `channels`, `account`, `ndr`, `imports`, `international`

See the [`tims/shiprocket-php-sdk` README](https://github.com/timslabs/shiprocket-php-sdk) for full endpoint coverage.

## Token cache

Tokens are cached under a key derived from the API user email. To force a fresh login:

```php
use Tims\LaravelShiprocket\Facades\Shiprocket;

Shiprocket::forgetToken();
```

## Webhooks

Shiprocket tracking webhooks are inbound POSTs to **your** app URL (configured in the Shiprocket panel). This package does not register webhook routes — add a controller/route in your application and optionally verify the `x-api-key` header. Use the PHP SDK client for outbound API calls; handle webhook payloads in your app.

## License

MIT
