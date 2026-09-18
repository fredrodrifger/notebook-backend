<?php

namespace Tests\Feature\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotesShowUpdateDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_note_is_returned_by_uuid_in_the_team_envelope(): void
    {
        $note = Note::factory()->create([NoteInterface::TITLE => 'Meeting notes']);

        $response = $this->getJson('/api/notes/'.$note->getUuid());

        $response->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('response.uuid', $note->getUuid())
            ->assertJsonPath('response.title', 'Meeting notes');
    }

    #[Test]
    public function an_unknown_uuid_returns_a_not_found_message(): void
    {
        $response = $this->getJson('/api/notes/'.Str::uuid());

        $response->assertNotFound()->assertJsonStructure(['message']);
    }

    #[Test]
    public function the_numeric_key_is_not_exposed_in_the_payload(): void
    {
        $note = Note::factory()->create();

        $response = $this->getJson('/api/notes/'.$note->getUuid());

        $response->assertOk()->assertJsonMissingPath('response.id');
    }

    #[Test]
    public function pinning_a_note_updates_it_and_moves_the_update_timestamp(): void
    {
        $note = Note::factory()->create();
        $updatedAtBefore = $note->getUpdatedAt()->toIso8601String();

        $this->travel(1)->minutes();

        $response = $this->patchJson('/api/notes/'.$note->getUuid(), [NoteInterface::IS_PINNED => true]);

        $response->assertOk()->assertJsonPath('response.is_pinned', true);
        $this->assertNotSame($updatedAtBefore, $response->json('response.updated_at'));
        $this->assertTrue($note->fresh()->getIsPinned());
    }

    #[Test]
    public function an_empty_tags_array_clears_the_tags(): void
    {
        $note = Note::factory()->create([NoteInterface::TAGS => ['work', 'urgent']]);

        $response = $this->patchJson('/api/notes/'.$note->getUuid(), [NoteInterface::TAGS => []]);

        $response->assertOk()->assertJsonPath('response.tags', []);
        $this->assertSame([], $note->fresh()->getTags());
    }

    #[Test]
    public function omitting_the_tags_key_keeps_the_stored_tags(): void
    {
        $note = Note::factory()->create([NoteInterface::TAGS => ['work']]);

        $response = $this->patchJson('/api/notes/'.$note->getUuid(), [NoteInterface::TITLE => 'Renamed']);

        $response->assertOk()
            ->assertJsonPath('response.title', 'Renamed')
            ->assertJsonPath('response.tags', ['work']);
    }

    #[Test]
    public function clearing_the_title_is_rejected(): void
    {
        $note = Note::factory()->create([NoteInterface::TITLE => 'Original']);

        $response = $this->patchJson('/api/notes/'.$note->getUuid(), [NoteInterface::TITLE => '   ']);

        $response->assertStatus(422)->assertJsonValidationErrors([NoteInterface::TITLE]);
        $this->assertSame('Original', $note->fresh()->getTitle());
    }

    #[Test]
    public function deleting_a_note_soft_deletes_it_and_then_it_is_gone(): void
    {
        $note = Note::factory()->create();

        $this->deleteJson('/api/notes/'.$note->getUuid())
            ->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', 'Deleted successfully');

        $this->assertSoftDeleted(NoteInterface::TABLE, [NoteInterface::ID => $note->getId()]);
        $this->getJson('/api/notes/'.$note->getUuid())->assertNotFound();
    }

    #[Test]
    public function notes_can_be_deleted_in_batch_by_uuid(): void
    {
        $notes = Note::factory()->count(3)->create();

        $response = $this->deleteJson('/api/notes', [
            'ids' => $notes->pluck(NoteInterface::UUID)->all(),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('count', 3)
            ->assertJsonPath('message', 'Deleted successfully');

        $this->assertSame(0, Note::query()->count());
    }

    #[Test]
    public function batch_delete_rejects_an_unknown_uuid(): void
    {
        $note = Note::factory()->create();

        $response = $this->deleteJson('/api/notes', [
            'ids' => [$note->getUuid(), (string) Str::uuid()],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['ids.1']);
        $this->assertSame(1, Note::query()->count());
    }

    #[Test]
    public function duplicating_a_note_copies_the_content_and_unpins_the_copy(): void
    {
        $note = Note::factory()->pinned()->create([
            NoteInterface::TITLE => 'Original',
            NoteInterface::CONTENT => '<p>body</p>',
            NoteInterface::TAGS => ['work'],
        ]);

        $response = $this->postJson('/api/notes/'.$note->getUuid().'/duplicate');

        $response->assertOk()
            ->assertJsonPath('response.title', 'Original (copy)')
            ->assertJsonPath('response.content', '<p>body</p>')
            ->assertJsonPath('response.tags', ['work'])
            ->assertJsonPath('response.is_pinned', false);

        $this->assertNotSame($note->getUuid(), $response->json('response.uuid'));
        $this->assertSame(2, Note::query()->count());
    }
}
