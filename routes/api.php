<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\Note\NotesController;
use App\Http\Controllers\Note\NoteStatsController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);

Route::get('tags', [TagController::class, 'index']);

Route::get('notes/stats', NoteStatsController::class);

Route::apiResource('notes', NotesController::class);
Route::delete('notes', [NotesController::class, 'batchDestroy']);
Route::post('notes/{note}/duplicate', [NotesController::class, 'duplicate']);
