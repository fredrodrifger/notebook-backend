<?php

namespace App\Http\Requests\Note;

use App\Http\Requests\BaseRequest;
use App\Interfaces\Models\Note\NoteInterface;
use Illuminate\Validation\Rule;

class NotesBatchDestroyRequest extends BaseRequest
{
    const IDS = 'ids';

    public function rules(): array
    {
        return [
            self::IDS => ['required', 'array', 'min:1'],
            self::IDS.'.*' => ['required', 'string', Rule::exists(NoteInterface::TABLE, NoteInterface::UUID)],
        ];
    }
}
