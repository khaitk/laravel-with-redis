<?php

namespace Laravel\Services;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

/**
 * Cache-aside with an atomic lock (anti stampede) and negative caching.
 */
class CacheAside
{
    private const NOT_FOUND = '__NOT_FOUND__';

    /**
     * @param  Closure():mixed  $loader  Returns the value, or null if it does not exist.
     * @param  int  $ttl  Seconds for a found value.
     * @param  int  $notFoundTtl  Seconds for the "not found" marker.
     */
    public function remember(string $key, Closure $loader, int $ttl, int $notFoundTtl = 15): CacheResult
    {
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $this->wrap($cached, 'redis');
        }

        $load = function () use ($key, $loader, $ttl, $notFoundTtl): CacheResult {
            $value = $loader();
            Cache::put($key, $value ?? self::NOT_FOUND, $value === null ? $notFoundTtl : $ttl);

            return new CacheResult($value, 'mysql');
        };

        try {
            return Cache::lock("lock:{$key}", 10)->block(5, function () use ($key, $load) {
                // Double-check: a previous request may have just filled the cache
                $existing = Cache::get($key);

                return $existing !== null ? $this->wrap($existing, 'redis') : $load();
            });
        } catch (LockTimeoutException) {
            // Lock holder too slow: fall back to a direct load
            return $load();
        }
    }

    private function wrap(mixed $cached, string $source): CacheResult
    {
        return new CacheResult($cached === self::NOT_FOUND ? null : $cached, $source);
    }
}
