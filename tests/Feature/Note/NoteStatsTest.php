<?php

namespace Tests\Feature\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoteStatsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_stats_endpoint_counts_notes_and_tags(): void
    {
        Note::factory()->create([NoteInterface::TAGS => ['work', 'urgent']]);
        Note::factory()->create([NoteInterface::TAGS => ['home']]);

        $response = $this->getJson('/api/notes/stats');

        $response->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonPath('response.notes_count', 2)
            ->assertJsonPath('response.tags_count', 3)
            ->assertJsonStructure(['response' => ['notes_count', 'tags_count', 'last_updated_at'], 'status']);
    }

    #[Test]
    public function the_stats_endpoint_answers_on_an_empty_database(): void
    {
        $response = $this->getJson('/api/notes/stats');

        $response->assertOk()
            ->assertJsonPath('response.notes_count', 0)
            ->assertJsonPath('response.tags_count', 0)
            ->assertJsonPath('response.last_updated_at', null);
    }

    #[Test]
    public function the_tags_endpoint_returns_each_tag_once_sorted(): void
    {
        Note::factory()->create([NoteInterface::TAGS => ['work', 'home']]);
        Note::factory()->create([NoteInterface::TAGS => ['home', 'urgent']]);

        $response = $this->getJson('/api/tags');

        $response->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonCount(3, 'response')
            ->assertJsonPath('response', ['home', 'urgent', 'work']);
    }
}
