<?php

namespace App\Actions\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;

class DuplicateNoteAction
{
    const TITLE_SUFFIX = ' (copy)';

    public function __construct(private Note $note) {}

    public function handle(): Note
    {
        /** @var Note $copy */
        $copy = $this->note->replicate([NoteInterface::ID, NoteInterface::UUID]);

        $copy->setTitle($this->copyTitle())->setIsPinned(false)->save();

        return $copy;
    }

    private function copyTitle(): string
    {
        $title = (string) $this->note->getTitle();
        $available = NoteInterface::TITLE_MAX_LENGTH - mb_strlen(self::TITLE_SUFFIX);

        return mb_substr($title, 0, $available).self::TITLE_SUFFIX;
    }
}
