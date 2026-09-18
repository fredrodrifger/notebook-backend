<?php

namespace Tests\Unit\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Traits\Request\ValidatesNote;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidatesNoteTest extends TestCase
{
    #[Test]
    public function the_store_rules_require_a_title_and_reject_an_over_long_tag(): void
    {
        $rules = (new class
        {
            use ValidatesNote;

            public function rules(): array
            {
                return $this->noteRules();
            }
        })->rules();

        $this->assertFalse(Validator::make([], $rules)->passes());

        $this->assertTrue(Validator::make([
            NoteInterface::TITLE => 'A title',
        ], $rules)->passes());

        $validator = Validator::make([
            NoteInterface::TITLE => 'A title',
            NoteInterface::TAGS => [str_repeat('a', NoteInterface::TAG_MAX_LENGTH + 1)],
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has(NoteInterface::TAGS.'.0'));
    }

    #[Test]
    public function the_update_rules_keep_every_field_optional(): void
    {
        $rules = (new class
        {
            use ValidatesNote;

            public function rules(): array
            {
                return $this->noteUpdateRules();
            }
        })->rules();

        $this->assertTrue(Validator::make([], $rules)->passes());
        $this->assertTrue(Validator::make([NoteInterface::IS_PINNED => true], $rules)->passes());
        $this->assertFalse(Validator::make([NoteInterface::IS_PINNED => 'not-a-bool'], $rules)->passes());
        $this->assertFalse(Validator::make([NoteInterface::TITLE => ''], $rules)->passes());
    }
}
