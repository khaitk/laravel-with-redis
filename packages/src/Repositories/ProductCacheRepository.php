<?php

namespace Laravel\Repositories;

use Illuminate\Support\Facades\Cache;
use Laravel\Services\CacheAside;
use Laravel\Services\CacheResult;

/**
 * Redis read layer in front of ProductRepository.
 * Owns every product cache key, including their invalidation.
 */
class ProductCacheRepository
{
    private const LIST_VERSION_KEY = 'catalog:products:version';

    public function __construct(
        private readonly ProductRepository $products,
        private readonly CacheAside $cache,
    ) {}

    public function paginate(int $page, int $perPage): CacheResult
    {
        // Version is bumped on writes; stale pages are ignored and expire by TTL
        $version = $this->listVersion();
        $key = "catalog:products:v{$version}:list:p{$page}:l{$perPage}";

        $result = $this->cache->remember(
            $key,
            fn () => $this->products->paginate($page, $perPage),
            ttl: 60 + random_int(1, 10), // jitter so pages don't expire together
        );

        return new CacheResult($result->data, $result->source, $version);
    }

    public function find(int $id): CacheResult
    {
        return $this->cache->remember(
            $this->itemKey($id),
            fn () => $this->products->find($id)?->toArray(),
            ttl: 300 + random_int(1, 30),
            notFoundTtl: 15, // protects MySQL from bogus-ID floods
        );
    }

    public function forget(int $id): void
    {
        Cache::forget($this->itemKey($id));
    }

    /**
     * Invalidate all list pages at once by bumping the namespace version (atomic).
     */
    public function invalidateLists(): void
    {
        // Readers default to version 1, so seed 1 then increment
        Cache::add(self::LIST_VERSION_KEY, 1);
        Cache::increment(self::LIST_VERSION_KEY);
    }

    private function listVersion(): int
    {
        return (int) Cache::get(self::LIST_VERSION_KEY, 1);
    }

    private function itemKey(int $id): string
    {
        return "catalog:product:{$id}";
    }
}
