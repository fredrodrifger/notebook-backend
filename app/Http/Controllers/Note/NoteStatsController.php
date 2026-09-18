<?php

namespace App\Http\Controllers\Note;

use App\Http\Controllers\Controller;
use App\Repositories\Note\NotesRepository;
use Illuminate\Http\JsonResponse;

class NoteStatsController extends Controller
{
    public function __construct(private NotesRepository $repository) {}

    public function __invoke(): JsonResponse
    {
        return $this->sendResponse($this->repository->generalStats());
    }
}
