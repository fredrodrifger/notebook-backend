<?php

namespace App\Http\Requests\Note;

use App\Http\Requests\BaseRequest;
use App\Traits\Request\ValidatesNote;

class NotesUpdateRequest extends BaseRequest
{
    use ValidatesNote;

    public function rules(): array
    {
        return $this->noteUpdateRules();
    }
}
