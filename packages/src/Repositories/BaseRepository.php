<?php

namespace Laravel\Repositories;

use Illuminate\Database\Eloquent\Model;
use Laravel\Contracts\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function find(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(int $id, array $data): ?Model
    {
        $record = $this->find($id);
        $record?->update($data);

        return $record;
    }

    public function delete(int $id): bool
    {
        return (bool) $this->find($id)?->delete();
    }
}
