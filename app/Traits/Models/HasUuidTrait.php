<?php

namespace App\Traits\Models;

use Illuminate\Support\Str;

trait HasUuidTrait
{
    protected static function bootHasUuidTrait(): void
    {
        static::creating(function ($model) {
            if (empty($model->{self::UUID})) {
                $model->{self::UUID} = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return self::UUID;
    }
}
