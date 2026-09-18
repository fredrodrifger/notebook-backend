<?php

namespace App\Http\Middleware;

use App\Constants\NotebookConstants;
use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class NotebookTokenMiddleware
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $expectedToken = (string) config('notebook.api_token');

        if ($expectedToken === '') {
            if (app()->isProduction()) {
                return $this->unauthorized(__('errors.api_token_not_configured'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $next($request);
        }

        $providedToken = (string) $request->header(NotebookConstants::TOKEN_HEADER, '');

        if (! hash_equals($expectedToken, $providedToken)) {
            return $this->unauthorized(__('errors.invalid_api_token'));
        }

        return $next($request);
    }

    private function unauthorized(string $message, int $status = Response::HTTP_UNAUTHORIZED): SymfonyResponse
    {
        return response()->json([
            Controller::MESSAGE => $message,
            Controller::ERRORS => [],
        ], $status);
    }
}
