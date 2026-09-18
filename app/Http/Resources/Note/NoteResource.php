<?php

namespace App\Http\Resources\Note;

use App\Http\Resources\BaseResource;
use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use App\Traits\Http\SuccessStatusTrait;
use Illuminate\Database\Eloquent\Model;

class NoteResource extends BaseResource
{
    use SuccessStatusTrait;

    private const MODEL = Note::class;

    protected function getCommonArray($request): array
    {
        return [
            NoteInterface::UUID => $this->getUuid(),
            NoteInterface::TITLE => $this->getTitle(),
            NoteInterface::CONTENT => $this->getContent(),
            NoteInterface::TAGS => $this->getTags() ?? [],
            NoteInterface::IS_PINNED => (bool) $this->getIsPinned(),
            Model::CREATED_AT => $this->getCreatedAt()?->toIso8601String(),
            Model::UPDATED_AT => $this->getUpdatedAt()?->toIso8601String(),
        ];
    }
}
