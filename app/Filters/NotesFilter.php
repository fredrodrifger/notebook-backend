<?php

namespace App\Filters;

use AliMousavi\Filoquent\Filters\FilterAbstract;
use App\Interfaces\Models\Note\NoteInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotesFilter extends FilterAbstract
{
    protected array $filterables = [
        'tag' => self::TYPE_STRING,
        'isPinned' => self::TYPE_BOOLEAN,
    ];

    protected array $searchables = [
        NoteInterface::TITLE,
        NoteInterface::CONTENT,
    ];

    protected array $orderables = [
        NoteInterface::TITLE,
        NoteInterface::IS_PINNED,
        Model::CREATED_AT,
        Model::UPDATED_AT,
    ];

    protected array $orderBy = [
        NoteInterface::IS_PINNED => 'desc',
        Model::UPDATED_AT => 'desc',
    ];

    protected function tag(string $tag): Builder
    {
        return $this->builder->whereJsonContains(NoteInterface::TAGS, $tag);
    }

    protected function isPinned(bool $isPinned): Builder
    {
        return $this->builder->where(NoteInterface::IS_PINNED, $isPinned);
    }
}
