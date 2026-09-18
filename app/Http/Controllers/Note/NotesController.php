<?php

namespace App\Http\Controllers\Note;

use App\Actions\Note\DuplicateNoteAction;
use App\Filters\NotesFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Note\NotesBatchDestroyRequest;
use App\Http\Requests\Note\NotesIndexRequest;
use App\Http\Requests\Note\NotesStoreRequest;
use App\Http\Requests\Note\NotesUpdateRequest;
use App\Http\Resources\Note\NoteResource;
use App\Models\Note\Note;
use App\Repositories\Note\NotesRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotesController extends Controller
{
    public function __construct(private NotesRepository $repository) {}

    public function index(NotesIndexRequest $request, NotesFilter $filter): AnonymousResourceCollection
    {
        $builder = $this->repository->index($request->user());
        $builder->filter($filter);

        return NoteResource::collection($builder->paginate($request->perPage()))->additional([
            self::STATUS => Response::HTTP_OK,
        ]);
    }

    public function store(NotesStoreRequest $request)
    {
        $note = $this->repository->create($request->noteData());

        return new NoteResource($note);
    }

    public function show(Note $note)
    {
        return new NoteResource($note);
    }

    public function update(NotesUpdateRequest $request, Note $note)
    {
        $updatedNote = $this->repository->update($note, $request->noteData());

        return new NoteResource($updatedNote);
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return $this->deleted();
    }

    public function batchDestroy(NotesBatchDestroyRequest $request): JsonResponse
    {
        $count = $this->repository->batchDestroy($request->validated(NotesBatchDestroyRequest::IDS));

        return $this->batchDeleted($count);
    }

    public function duplicate(Note $note)
    {
        return new NoteResource(run(new DuplicateNoteAction($note)));
    }
}
