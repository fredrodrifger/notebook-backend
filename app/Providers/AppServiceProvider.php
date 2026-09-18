<?php

namespace App\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Single resources serialize as {response: {...}, status: 200} — the team's envelope.
        JsonResource::wrap(Controller::RESPONSE);
    }

    public function boot(): void
    {
        //
    }
}
