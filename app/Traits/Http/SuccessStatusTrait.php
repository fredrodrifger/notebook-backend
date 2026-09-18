<?php

namespace App\Traits\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

trait SuccessStatusTrait
{
    public function with($request): array
    {
        return [
            Controller::STATUS => Response::HTTP_OK,
        ];
    }

    /**
     * Laravel answers 201 for a model that was just created. The team's envelope answers 200 for
     * writes and keeps the status in the body, so the transport status is pinned here once for
     * every resource that uses this trait instead of being patched per controller action.
     */
    public function toResponse($request)
    {
        return parent::toResponse($request)->setStatusCode(Response::HTTP_OK);
    }

    public function whenLoadedAndNotEmpty($relationship)
    {
        if (! $this->resource->relationLoaded($relationship)) {
            return new MissingValue;
        }

        $loadedValue = $this->resource->{$relationship};

        if ($loadedValue instanceof Collection && $loadedValue->isEmpty()) {
            return new MissingValue;
        }

        return $loadedValue;
    }
}
