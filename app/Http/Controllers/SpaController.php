<?php

namespace App\Http\Controllers;

use App\Constants\NotebookConstants;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves the built single-page app so the domain has one origin: the SPA and the API share the port.
 * The file is the frontend build published into `public/` by `scripts/deploy-live.sh`.
 */
class SpaController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        $indexFile = public_path(NotebookConstants::SPA_INDEX);

        abort_unless(is_file($indexFile), Response::HTTP_NOT_FOUND, 'The frontend build is missing.');

        return response()->file($indexFile);
    }
}
