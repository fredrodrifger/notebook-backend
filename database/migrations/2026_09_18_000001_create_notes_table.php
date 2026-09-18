<?php

use App\Interfaces\Models\Note\NoteInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(NoteInterface::TABLE, function (Blueprint $table) {
            $table->id();
            $table->uuid(NoteInterface::UUID)->unique();
            $table->string(NoteInterface::TITLE, NoteInterface::TITLE_MAX_LENGTH);
            $table->text(NoteInterface::CONTENT)->nullable();
            $table->json(NoteInterface::TAGS)->nullable();
            $table->boolean(NoteInterface::IS_PINNED)->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index([NoteInterface::IS_PINNED, Model::UPDATED_AT]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(NoteInterface::TABLE);
    }
};
