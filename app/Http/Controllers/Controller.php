<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    const DEFAULT_PAGE_SIZE = 10;

    const FILTERS = 'filters';

    const QUERYABLES = 'queryables';

    const PER_PAGE = 'per_page';

    const STATUS = 'status';

    const ERRORS = 'errors';

    const MODEL = 'model';

    const RESPONSE = 'response';

    const MESSAGE = 'message';

    public function sendResponse(
        array|null|AnonymousResourceCollection $content = [],
        ?string $message = null,
        int $status = Response::HTTP_OK,
        array $headers = []): JsonResponse
    {
        $response = [
            self::RESPONSE => $content,
            self::STATUS => $status,
        ];

        if ($message) {
            $response[self::MESSAGE] = $message;
        }

        return response()->json($response, $status, $headers);
    }

    public function sendErrorResponse(
        ?string $message = null,
        int $status = Response::HTTP_UNAUTHORIZED,
        array $errors = [],
        ?array $response = null,
    ): JsonResponse {
        $toResponse = [
            self::MESSAGE => $message,
            self::ERRORS => $errors,
        ];

        if ($response) {
            $toResponse[self::RESPONSE] = $response;
        }

        return response()->json($toResponse, $status);
    }

    protected function deleted(): JsonResponse
    {
        return response()->json([
            self::MESSAGE => __('errors.deleted_successfully'),
            self::STATUS => Response::HTTP_OK,
        ], Response::HTTP_OK);
    }

    protected function batchDeleted(int $count = 0): JsonResponse
    {
        return response()->json([
            self::MESSAGE => __('errors.deleted_successfully'),
            'count' => $count,
            self::STATUS => Response::HTTP_OK,
        ], Response::HTTP_OK);
    }
}
