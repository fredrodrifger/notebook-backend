<?php

return [
    /**
     * Shared secret for the single-user notebook. Generate one with
     * `openssl rand -hex 24` and send it in the X-Notebook-Token header.
     *
     * Empty in local development (the API stays open on 127.0.0.1). In production an empty token
     * refuses every request instead of silently opening the door.
     */
    'api_token' => env('NOTEBOOK_API_TOKEN'),
];
