<?php

namespace App\Http\Requests\Note;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequest;
use App\Interfaces\Models\Note\NoteInterface;
use Illuminate\Validation\Rule;

class NotesIndexRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            Controller::PER_PAGE => ['sometimes', 'integer', 'min:1'],
            'search' => ['sometimes', 'nullable', 'string', 'max:'.NoteInterface::TITLE_MAX_LENGTH],
            'tag' => ['sometimes', 'nullable', 'string', 'max:'.NoteInterface::TAG_MAX_LENGTH],
            'isPinned' => ['sometimes', Rule::in([true, false, 1, 0, '1', '0', 'true', 'false'])],
            'orderBy' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
