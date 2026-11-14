<?php

namespace Laravel\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared CRUD contract for every repository. Per-model interfaces are only
 * worth adding when a model needs a swappable implementation.
 */
interface RepositoryInterface
{
    public function find(int $id): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): ?Model;

    public function delete(int $id): bool;
}
