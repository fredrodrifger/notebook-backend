<?php

namespace Tests\Feature\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotesStoreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_note_is_created_and_returned_in_the_team_envelope(): void
    {
        $response = $this->postJson('/api/notes', [
            NoteInterface::TITLE => 'Shopping list',
            NoteInterface::CONTENT => '<p>milk</p>',
            NoteInterface::TAGS => ['personal'],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('response.title', 'Shopping list')
            ->assertJsonPath('response.content', '<p>milk</p>')
            ->assertJsonPath('response.tags', ['personal'])
            ->assertJsonPath('response.is_pinned', false)
            ->assertJsonStructure([
                'response' => ['uuid', 'title', 'content', 'tags', 'is_pinned', 'created_at', 'updated_at'],
                'status',
            ]);

        $this->assertSame(1, Note::query()->count());
        $this->assertTrue(Note::query()->firstOrFail()->getUuid() === $response->json('response.uuid'));
    }

    #[Test]
    public function an_empty_title_is_rejected(): void
    {
        $response = $this->postJson('/api/notes', [NoteInterface::TITLE => '']);

        $response->assertStatus(422)->assertJsonValidationErrors([NoteInterface::TITLE]);
        $this->assertSame(0, Note::query()->count());
    }

    #[Test]
    public function a_whitespace_only_title_is_rejected(): void
    {
        $response = $this->postJson('/api/notes', [NoteInterface::TITLE => '    ']);

        $response->assertStatus(422)->assertJsonValidationErrors([NoteInterface::TITLE]);
        $this->assertSame(0, Note::query()->count());
    }

    #[Test]
    public function the_title_is_trimmed_before_it_is_stored(): void
    {
        $response = $this->postJson('/api/notes', [NoteInterface::TITLE => '  Padded title  ']);

        $response->assertOk()->assertJsonPath('response.title', 'Padded title');
    }

    #[Test]
    public function tags_are_trimmed_deduplicated_and_emptied_out(): void
    {
        $response = $this->postJson('/api/notes', [
            NoteInterface::TITLE => 'Tagged',
            NoteInterface::TAGS => ['  work  ', 'work', '', 'idea'],
        ]);

        $response->assertOk()->assertJsonPath('response.tags', ['work', 'idea']);
    }

    #[Test]
    public function a_title_longer_than_the_interface_limit_is_rejected(): void
    {
        $response = $this->postJson('/api/notes', [
            NoteInterface::TITLE => str_repeat('a', NoteInterface::TITLE_MAX_LENGTH + 1),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([NoteInterface::TITLE]);
    }

    #[Test]
    public function an_over_long_tag_is_rejected(): void
    {
        $response = $this->postJson('/api/notes', [
            NoteInterface::TITLE => 'Tagged',
            NoteInterface::TAGS => [str_repeat('a', NoteInterface::TAG_MAX_LENGTH + 1)],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([NoteInterface::TAGS.'.0']);
    }
}
