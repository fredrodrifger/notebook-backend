<?php

namespace Database\Factories\Note;

use App\Interfaces\Models\Note\NoteInterface;
use App\Models\Note\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            NoteInterface::TITLE => $this->faker->sentence(3),
            NoteInterface::CONTENT => '<p>'.$this->faker->paragraph().'</p>',
            NoteInterface::TAGS => $this->faker->randomElements(['work', 'idea', 'personal'], 2),
            NoteInterface::IS_PINNED => false,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            NoteInterface::IS_PINNED => true,
        ]);
    }
}
