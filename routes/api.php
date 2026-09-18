<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\Note\NotesController;
use App\Http\Controllers\Note\NoteStatsController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\NotebookTokenMiddleware;
use Illuminate\Support\Facades\Route;

/*
| The health probe stays open (the local service script polls it before the UI starts); every other
| endpoint sits behind the shared token.
*/
Route::get('health', HealthController::class);

Route::middleware(NotebookTokenMiddleware::class)->group(function () {
    Route::get('tags', [TagController::class, 'index']);

    Route::get('notes/stats', NoteStatsController::class);

    Route::apiResource('notes', NotesController::class);
    Route::delete('notes', [NotesController::class, 'batchDestroy']);
    Route::post('notes/{note}/duplicate', [NotesController::class, 'duplicate']);
});
