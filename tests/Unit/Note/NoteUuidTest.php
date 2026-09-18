<?php

namespace Tests\Unit\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoteUuidTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function creating_a_note_generates_a_uuid(): void
    {
        $note = Note::factory()->create();

        $this->assertNotNull($note->getUuid());
        $this->assertTrue(Str::isUuid($note->getUuid()));
    }

    #[Test]
    public function a_given_uuid_is_not_overwritten(): void
    {
        $uuid = (string) Str::uuid();

        $note = Note::factory()->create([NoteInterface::UUID => $uuid]);

        $this->assertSame($uuid, $note->getUuid());
    }

    #[Test]
    public function routes_bind_notes_by_uuid(): void
    {
        $note = Note::factory()->create();

        $this->assertSame(NoteInterface::UUID, $note->getRouteKeyName());
        $this->assertSame($note->getUuid(), $note->getRouteKey());
    }
}
