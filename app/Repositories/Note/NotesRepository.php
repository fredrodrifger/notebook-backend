<?php

namespace App\Repositories\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class NotesRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Note;
    }

    public function batchDestroy(array $uuids): int
    {
        return Note::query()->whereIn(NoteInterface::UUID, $uuids)->delete();
    }

    public function tags(): array
    {
        return Note::query()
            ->pluck(NoteInterface::TAGS)
            ->flatMap(fn (?array $noteTags) => $noteTags ?? [])
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function generalStats(): array
    {
        $latestNote = Note::query()->latest(Model::UPDATED_AT)->first();

        return [
            'notes_count' => Note::query()->count(),
            'tags_count' => count($this->tags()),
            'last_updated_at' => $latestNote?->getUpdatedAt()?->toIso8601String(),
        ];
    }
}
