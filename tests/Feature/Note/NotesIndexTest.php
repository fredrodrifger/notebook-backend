<?php

namespace Tests\Feature\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotesIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_index_returns_the_rows_under_response_and_the_pagination_keys_next_to_it(): void
    {
        Note::factory()->count(3)->create();

        $response = $this->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonPath('status', 200)
            ->assertJsonCount(3, 'response')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonStructure([
                'response' => [['uuid', 'title', 'content', 'tags', 'is_pinned', 'created_at', 'updated_at']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                'status',
            ]);
    }

    #[Test]
    public function the_second_page_holds_the_remainder_of_the_rows(): void
    {
        Note::factory()->count(12)->create();

        $response = $this->getJson('/api/notes?page=2&per_page=10');

        $response->assertOk()
            ->assertJsonCount(2, 'response')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.current_page', 2);
    }

    #[Test]
    public function the_page_size_is_capped_at_the_controller_default(): void
    {
        Note::factory()->count(12)->create();

        $response = $this->getJson('/api/notes?per_page=50');

        $response->assertOk()
            ->assertJsonCount(10, 'response')
            ->assertJsonPath('meta.per_page', 10);
    }

    #[Test]
    public function search_matches_the_title_and_the_content(): void
    {
        Note::factory()->create([NoteInterface::TITLE => 'Groceries', NoteInterface::CONTENT => '<p>bread</p>']);
        Note::factory()->create([NoteInterface::TITLE => 'Ideas', NoteInterface::CONTENT => '<p>buy bread tomorrow</p>']);
        Note::factory()->create([NoteInterface::TITLE => 'Other', NoteInterface::CONTENT => '<p>nothing here</p>']);

        $response = $this->getJson('/api/notes?search=bread');

        $response->assertOk()
            ->assertJsonCount(2, 'response')
            ->assertJsonPath('meta.total', 2);
    }

    #[Test]
    public function the_tag_filter_matches_a_single_tag(): void
    {
        Note::factory()->create([NoteInterface::TITLE => 'Work note', NoteInterface::TAGS => ['work', 'urgent']]);
        Note::factory()->create([NoteInterface::TITLE => 'Home note', NoteInterface::TAGS => ['home']]);
        Note::factory()->create([NoteInterface::TITLE => 'Untagged', NoteInterface::TAGS => []]);

        $response = $this->getJson('/api/notes?tag=urgent');

        $response->assertOk()
            ->assertJsonCount(1, 'response')
            ->assertJsonPath('response.0.title', 'Work note');
    }

    #[Test]
    public function the_is_pinned_filter_only_returns_pinned_notes(): void
    {
        Note::factory()->create([NoteInterface::TITLE => 'Pinned']);
        Note::factory()->pinned()->create([NoteInterface::TITLE => 'Important']);

        $response = $this->getJson('/api/notes?isPinned=true');

        $response->assertOk()
            ->assertJsonCount(1, 'response')
            ->assertJsonPath('response.0.title', 'Important')
            ->assertJsonPath('response.0.is_pinned', true);
    }

    #[Test]
    public function pinned_notes_are_listed_first_then_the_most_recently_updated(): void
    {
        $older = Note::factory()->create([NoteInterface::TITLE => 'Older']);
        $newer = Note::factory()->create([NoteInterface::TITLE => 'Newer']);
        $pinned = Note::factory()->pinned()->create([NoteInterface::TITLE => 'Pinned']);

        $this->travel(1)->minutes();
        $older->setTitle('Older (touched)')->save();

        $response = $this->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonPath('response.0.title', 'Pinned')
            ->assertJsonPath('response.1.title', 'Older (touched)')
            ->assertJsonPath('response.2.title', $newer->getTitle());
        $this->assertSame($pinned->getUuid(), $response->json('response.0.uuid'));
    }

    #[Test]
    public function an_unknown_query_parameter_is_ignored_instead_of_failing(): void
    {
        Note::factory()->count(2)->create();

        $response = $this->getJson('/api/notes?somethingUnknown=1');

        $response->assertOk()->assertJsonPath('meta.total', 2);
    }
}
