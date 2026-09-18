<?php

namespace App\Repositories;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function query(): Builder
    {
        return $this->model::query();
    }

    /**
     * The single entry point for list endpoints. The studied backend dispatches here on the caller
     * category (admin / user / customer / platform admin). This application has no authentication,
     * so every caller arrives as null and lands in the public branch — the signature is kept so
     * scoping can be introduced later without touching a single controller.
     */
    public function index(?object $user): Builder
    {
        if (! $user) {
            return $this->getPublicIndex($this->query(), $user);
        }

        return $this->getAdminIndex($this->query(), $user);
    }

    protected function getAdminIndex(Builder $builder, object $user): Builder
    {
        return $builder;
    }

    protected function getPublicIndex(Builder $builder, ?object $user = null): Builder
    {
        return $builder;
    }

    public function canIndex(?object $user, BaseModel $model): bool
    {
        return $this->index($user)->where($model->getKeyName(), $model->getKey())->exists();
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes)->save();

        return $model;
    }
}
