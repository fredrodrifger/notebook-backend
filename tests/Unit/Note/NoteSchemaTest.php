<?php

namespace Tests\Unit\Note;

use App\Constants\ConnectionConstants;
use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoteSchemaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function every_note_constant_exists_as_a_column(): void
    {
        $columns = [
            NoteInterface::ID,
            NoteInterface::UUID,
            NoteInterface::TITLE,
            NoteInterface::CONTENT,
            NoteInterface::TAGS,
            NoteInterface::IS_PINNED,
            Model::CREATED_AT,
            Model::UPDATED_AT,
            NoteInterface::DELETED_AT,
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn(NoteInterface::TABLE, $column),
                "The notes table is missing the column [{$column}]."
            );
        }
    }

    #[Test]
    public function the_model_declares_its_connection_table_and_casts(): void
    {
        $note = new Note;

        $this->assertSame(ConnectionConstants::APP_CONNECTION, $note->getConnectionName());
        $this->assertSame(NoteInterface::TABLE, $note->getTable());
        $this->assertSame('array', $note->getCasts()[NoteInterface::TAGS]);
        $this->assertSame('boolean', $note->getCasts()[NoteInterface::IS_PINNED]);
    }

    #[Test]
    public function soft_deletes_are_enabled_for_notes(): void
    {
        $note = Note::factory()->create();
        $note->delete();

        $this->assertSoftDeleted(NoteInterface::TABLE, [NoteInterface::ID => $note->getId()]);
        $this->assertSame(1, Note::withTrashed()->count());
        $this->assertSame(0, Note::query()->count());
    }
}
