<?php

namespace Laravel\Repositories;

use Laravel\Models\Product;

/**
 * MySQL source of truth. Knows nothing about caching.
 *
 * @property Product $model
 */
class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function paginate(int $page, int $perPage): array
    {
        // Eloquent's toArray() yields plain arrays (no stdClass), safe to cache in Redis
        return $this->model->newQuery()
            ->orderByDesc('id')
            ->paginate($perPage, ['id', 'name', 'price'], 'page', $page)
            ->toArray();
    }
}
