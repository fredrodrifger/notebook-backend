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
        return $this->sendResponse([
            'state' => 'ok',
            'database' => db_connection_ok() ? 'ok' : 'unavailable',
            'notes_table' => $this->notesTableState(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'checked_at' => now()->toIso8601String(),
        ], null, Response::HTTP_OK);
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
