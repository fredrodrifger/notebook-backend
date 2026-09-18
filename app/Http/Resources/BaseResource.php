<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Audience-aware output. The studied backend merges getCommonArray() with one per-role array
 * (getAdminArray / getUserArray / getCustomerArray / getPlatformAdminArray). This application has no
 * authentication, so there is a single public audience; the dispatch shape is kept so a role array
 * can be added without touching the common fields.
 */
abstract class BaseResource extends JsonResource
{
    public function toArray($request): array
    {
        $commonResponse = $this->getCommonArray($request);
        $specialResponse = $this->getPublicArray($request);

        return array_merge($commonResponse, $specialResponse);
    }

    protected function getCommonArray($request): array
    {
        return [];
    }

    protected function getPublicArray($request): array
    {
        return [];
    }
}
