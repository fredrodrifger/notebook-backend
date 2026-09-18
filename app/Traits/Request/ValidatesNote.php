<?php

namespace App\Traits\Request;

use App\Interfaces\Models\Note\NoteInterface;

trait ValidatesNote
{
    protected function noteRules(): array
    {
        return [
            NoteInterface::TITLE => ['required', 'string', 'max:'.NoteInterface::TITLE_MAX_LENGTH],
            NoteInterface::CONTENT => ['sometimes', 'nullable', 'string'],
            NoteInterface::TAGS => ['sometimes', 'array'],
            NoteInterface::TAGS.'.*' => ['sometimes', 'nullable', 'string', 'max:'.NoteInterface::TAG_MAX_LENGTH],
        ];
    }

    protected function noteUpdateRules(): array
    {
        return [
            NoteInterface::TITLE => ['sometimes', 'required', 'string', 'max:'.NoteInterface::TITLE_MAX_LENGTH],
            NoteInterface::CONTENT => ['sometimes', 'nullable', 'string'],
            NoteInterface::TAGS => ['sometimes', 'array'],
            NoteInterface::TAGS.'.*' => ['sometimes', 'nullable', 'string', 'max:'.NoteInterface::TAG_MAX_LENGTH],
            NoteInterface::IS_PINNED => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Turns the validated tag list into the exact shape the persistence layer stores: trimmed,
     * without empty entries and without duplicates. The controller stays a one-liner.
     */
    public function tagData(): array
    {
        $tags = [];

        foreach ($this->validated(NoteInterface::TAGS, []) as $tag) {
            $tag = trim($tag);

            if ($tag === '' || in_array($tag, $tags)) {
                continue;
            }

            $tags[] = $tag;
        }

        return $tags;
    }

    /**
     * "Absent vs empty": a missing tags key leaves the stored tags untouched, an empty array clears
     * them. Only a present key is normalised into the payload.
     */
    public function noteData(): array
    {
        $data = $this->safeValidated();

        if ($this->has(NoteInterface::TAGS)) {
            $data[NoteInterface::TAGS] = $this->tagData();
        }

        return $data;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has(NoteInterface::TITLE)) {
            $this->merge([
                NoteInterface::TITLE => trim((string) $this->input(NoteInterface::TITLE)),
            ]);
        }
    }
}
