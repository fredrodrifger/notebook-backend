<?php

use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

/*
| The frontend build is served from the same origin, so deep links such as /fa/notes/… answer with
| the SPA shell. /api and the health probe are excluded — they own their own routes.
*/
Route::get('/', SpaController::class);
Route::get('{any}', SpaController::class)->where('any', '^(?!api$|api/|up$).*$');
