<?php

namespace Tests\Feature\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotesPayloadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_note_created_without_content_returns_an_empty_string_not_null(): void
    {
        $response = $this->postJson('/api/notes', [
            NoteInterface::TITLE => 'Only a title',
        ]);

        $response->assertOk()
            ->assertJsonPath('response.content', '')
            ->assertJsonPath('response.tags', []);

        $this->assertIsString($response->json('response.content'));
    }

    #[Test]
    public function the_index_never_returns_null_content_or_null_tags(): void
    {
        Note::factory()->create([NoteInterface::CONTENT => null, NoteInterface::TAGS => null]);
        Note::factory()->create([NoteInterface::CONTENT => null, NoteInterface::TAGS => null]);

        $response = $this->getJson('/api/notes');

        $response->assertOk();

        foreach ($response->json('response') as $row) {
            $this->assertIsString($row['content']);
            $this->assertIsArray($row['tags']);
        }
    }
}
