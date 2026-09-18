<?php

namespace Tests\Unit\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoteMagicMethodsTest extends TestCase
{
    #[Test]
    public function setters_and_getters_are_derived_from_the_interface_constants(): void
    {
        $note = new Note;

        $note->setTitle('Shopping list')
            ->setContent('<p>milk</p>')
            ->setTags(['personal'])
            ->setIsPinned(true);

        $this->assertSame('Shopping list', $note->getTitle());
        $this->assertSame('<p>milk</p>', $note->getContent());
        $this->assertSame(['personal'], $note->getTags());
        $this->assertTrue($note->getIsPinned());
    }

    #[Test]
    public function two_word_columns_keep_their_snake_case_mapping(): void
    {
        $note = new Note;
        $note->setIsPinned(false);

        $this->assertFalse($note->getIsPinned());
        $this->assertArrayHasKey(NoteInterface::IS_PINNED, $note->getAttributes());
    }

    #[Test]
    public function unknown_methods_still_reach_the_query_builder(): void
    {
        $builder = (new Note)->where(NoteInterface::IS_PINNED, false);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertSame(NoteInterface::TABLE, $builder->getModel()->getTable());
    }

    #[Test]
    public function the_resource_class_is_derived_from_the_model_namespace(): void
    {
        $this->assertSame('App\Http\Resources\Note\NoteResource', (new Note)->getResourceClass());
    }
}
