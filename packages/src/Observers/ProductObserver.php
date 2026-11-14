<?php

namespace Laravel\Observers;

use Laravel\Models\Product;
use Laravel\Repositories\ProductCacheRepository;

class ProductObserver
{
    /**
     * Run handlers only after the surrounding DB transaction commits, so a
     * concurrent reader can't re-cache stale rows between forget and commit.
     */
    public bool $afterCommit = true;

    public function __construct(private readonly ProductCacheRepository $cache) {}

    public function created(Product $product): void
    {
        $this->cache->invalidateLists();
    }

    public function updated(Product $product): void
    {
        $this->cache->forget($product->id);

        // List pages only show name and price
        if ($product->wasChanged(['name', 'price'])) {
            $this->cache->invalidateLists();
        }
    }

    public function deleted(Product $product): void
    {
        $this->cache->forget($product->id);
        $this->cache->invalidateLists();
    }
}
