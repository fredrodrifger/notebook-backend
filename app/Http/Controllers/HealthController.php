<?php

namespace App\Http\Controllers;

use App\Interfaces\Models\Note\NoteInterface;
use App\Repositories\Note\NotesRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $payload = [
            'state' => 'ok',
            'database' => db_connection_ok() ? 'ok' : 'unavailable',
            'notes_table' => $this->notesTableState(),
            'checked_at' => now()->toIso8601String(),
        ];

        // The probe is open by design (the service script polls it before the UI starts), so the
        // framework and PHP versions are only disclosed outside production.
        if (! app()->isProduction()) {
            $payload['laravel_version'] = app()->version();
            $payload['php_version'] = PHP_VERSION;
        }

        return $this->sendResponse($payload, null, Response::HTTP_OK);
    }

    private function notesTableState(): string
    {
        try {
            app(NotesRepository::class)->query()->limit(1)->count();

            return NoteInterface::TABLE;
        } catch (Throwable) {
            return 'unavailable';
        }
    }
}
