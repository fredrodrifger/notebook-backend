<?php

namespace Tests\Feature;

use App\Models\Note\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotebookTokenTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-token-value';

    #[Test]
    public function the_api_refuses_requests_without_the_token_when_one_is_configured(): void
    {
        config()->set('notebook.api_token', self::TOKEN);

        $this->getJson('/api/notes')
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonPath('message', 'The access token is missing or wrong');
    }

    #[Test]
    public function a_wrong_token_is_refused(): void
    {
        config()->set('notebook.api_token', self::TOKEN);

        $this->getJson('/api/notes', ['X-Notebook-Token' => 'not-the-token'])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function the_correct_token_opens_every_protected_route(): void
    {
        config()->set('notebook.api_token', self::TOKEN);
        Note::factory()->count(2)->create();

        $this->getJson('/api/notes', ['X-Notebook-Token' => self::TOKEN])
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/tags', ['X-Notebook-Token' => self::TOKEN])->assertOk();
        $this->getJson('/api/notes/stats', ['X-Notebook-Token' => self::TOKEN])->assertOk();
    }

    #[Test]
    public function the_health_probe_stays_open(): void
    {
        config()->set('notebook.api_token', self::TOKEN);

        $this->getJson('/api/health')->assertOk()->assertJsonPath('response.state', 'ok');
    }

    #[Test]
    public function an_empty_token_leaves_the_local_api_open(): void
    {
        config()->set('notebook.api_token', null);

        $this->getJson('/api/notes')->assertOk();
    }

    #[Test]
    public function an_empty_token_refuses_everything_in_production(): void
    {
        config()->set('notebook.api_token', null);
        $this->app['env'] = 'production';

        $this->getJson('/api/notes')
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJsonPath('message', 'No access token is configured on the server');
    }

    #[Test]
    public function the_health_probe_hides_the_versions_in_production(): void
    {
        $this->app['env'] = 'production';

        $response = $this->getJson('/api/health');

        $response->assertOk()->assertJsonMissingPath('response.laravel_version');
        $response->assertJsonMissingPath('response.php_version');
    }

    #[Test]
    public function deep_links_answer_with_the_spa_shell(): void
    {
        $indexFile = public_path('index.html');
        $createdHere = ! is_file($indexFile);

        if ($createdHere) {
            file_put_contents($indexFile, '<!doctype html><html><body>notebook</body></html>');
        }

        try {
            $this->get('/fa/notes/1234')
                ->assertOk()
                ->assertHeader('content-type', 'text/html; charset=UTF-8');
        } finally {
            if ($createdHere) {
                unlink($indexFile);
            }
        }
    }

    #[Test]
    public function the_spa_route_does_not_shadow_the_api(): void
    {
        config()->set('notebook.api_token', self::TOKEN);

        $this->getJson('/api/health')->assertOk();
        $this->getJson('/api/notes', ['X-Notebook-Token' => self::TOKEN])->assertOk();
    }
}
