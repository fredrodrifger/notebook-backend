<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function health_reports_a_reachable_database_in_the_team_envelope(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonPath('response.state', 'ok')
            ->assertJsonPath('response.database', 'ok')
            ->assertJsonPath('response.notes_table', 'notes')
            ->assertJsonPath('status', 200)
            ->assertJsonStructure([
                'response' => ['state', 'database', 'laravel_version', 'php_version', 'checked_at'],
                'status',
            ]);
    }
}
