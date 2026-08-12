<?php

declare(strict_types=1);

namespace Tims\LaravelShiprocket\Support;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

/**
 * Laravel-backed cache for Shiprocket JWT access tokens.
 */
class LaravelTokenCache
{
    private const TAG = 'shiprocket-tokens';

    public function __construct(
        private readonly int|string $credentialsId = 'default',
    ) {}

    public function get(string $key): ?string
    {
        $value = $this->store()->get($this->prefixedKey($key));

        return is_string($value) ? $value : null;
    }

    public function set(string $key, string $value, int $ttl = 0): void
    {
        if ($ttl > 0) {
            $this->store()->put($this->prefixedKey($key), $value, $ttl);
        } else {
            $this->store()->forever($this->prefixedKey($key), $value);
        }
    }

    public function remove(string $key): void
    {
        $this->store()->forget($this->prefixedKey($key));
    }

    public function clearForCreds(): void
    {
        if ($this->isTaggable()) {
            Cache::tags([$this->credentialsTag()])->flush();
        }
    }

    private function prefixedKey(string $key): string
    {
        return 'shiprocket:'.$this->credentialsId.':'.hash('sha256', $key);
    }

    private function credentialsTag(): string
    {
        return 'creds'.$this->credentialsId;
    }

    private function store()
    {
        if ($this->isTaggable()) {
            return Cache::tags([self::TAG, $this->credentialsTag()]);
        }

        return Cache::store();
    }

    private function isTaggable(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }
}
