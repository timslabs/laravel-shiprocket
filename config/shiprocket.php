<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Create an API user in Shiprocket: Settings → API → Configure.
    | Use that email and password here (not your panel login).
    |
    */

    'email' => env('SHIPROCKET_EMAIL'),

    'password' => env('SHIPROCKET_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    */

    'base_url' => env('SHIPROCKET_BASE_URL', 'https://apiv2.shiprocket.in'),

    /*
    |--------------------------------------------------------------------------
    | Access Token Cache
    |--------------------------------------------------------------------------
    |
    | Shiprocket JWTs are valid for 10 days (240 hours). Tokens are cached in
    | Laravel's default cache store. TTL uses the documented lifetime minus a
    | safety buffer unless overridden.
    |
    */

    'token_cache' => [
        'enabled' => (bool) env('SHIPROCKET_TOKEN_CACHE', true),
        'default_ttl_seconds' => (int) env('SHIPROCKET_TOKEN_TTL', 864000),
        'expiration_buffer_seconds' => (int) env('SHIPROCKET_TOKEN_BUFFER', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Retries
    |--------------------------------------------------------------------------
    |
    | Transient 429 / 5xx responses are retried with exponential backoff.
    |
    */

    'retry' => [
        'enabled' => (bool) env('SHIPROCKET_RETRY_ENABLED', true),
        'max_attempts' => (int) env('SHIPROCKET_RETRY_MAX_ATTEMPTS', 3),
        'base_delay_ms' => (int) env('SHIPROCKET_RETRY_BASE_DELAY_MS', 500),
        'status_codes' => [429, 500, 502, 503, 504],
    ],

    'debug' => (bool) env('SHIPROCKET_DEBUG', false),

    'http' => [
        'timeout' => (float) env('SHIPROCKET_HTTP_TIMEOUT', 60),
    ],
];
