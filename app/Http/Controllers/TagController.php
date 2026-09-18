<?php

namespace App\Http\Controllers;

use App\Repositories\Note\NotesRepository;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function __construct(private NotesRepository $repository) {}

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->repository->tags());
    }
}
