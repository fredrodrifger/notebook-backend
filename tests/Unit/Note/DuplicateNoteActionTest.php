<?php

namespace Tests\Unit\Note;

use App\Actions\Note\DuplicateNoteAction;
use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DuplicateNoteActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_copy_gets_a_new_uuid_and_a_suffixed_title(): void
    {
        $note = Note::factory()->pinned()->create([
            NoteInterface::TITLE => 'Groceries',
            NoteInterface::CONTENT => '<p>milk</p>',
            NoteInterface::TAGS => ['home'],
        ]);

        $copy = run(new DuplicateNoteAction($note));

        $this->assertNotSame($note->getUuid(), $copy->getUuid());
        $this->assertSame('Groceries'.DuplicateNoteAction::TITLE_SUFFIX, $copy->getTitle());
        $this->assertSame('<p>milk</p>', $copy->getContent());
        $this->assertSame(['home'], $copy->getTags());
        $this->assertFalse($copy->getIsPinned());
        $this->assertTrue($note->fresh()->getIsPinned());
        $this->assertSame(2, Note::query()->count());
    }

    #[Test]
    public function the_suffixed_title_never_exceeds_the_interface_limit(): void
    {
        $note = Note::factory()->create([
            NoteInterface::TITLE => str_repeat('a', NoteInterface::TITLE_MAX_LENGTH),
        ]);

        $copy = run(new DuplicateNoteAction($note));

        $this->assertSame(NoteInterface::TITLE_MAX_LENGTH, mb_strlen($copy->getTitle()));
    }
}
