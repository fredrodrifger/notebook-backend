<?php

namespace App\Models\Note;

use App\Constants\ConnectionConstants;
use App\Interfaces\Models\Note\NoteInterface;
use App\Models\BaseModel;
use App\Traits\Models\HasUuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends BaseModel implements NoteInterface
{
    use HasUuidTrait;
    use SoftDeletes;

    protected $table = self::TABLE;

    protected $connection = ConnectionConstants::APP_CONNECTION;

    protected $guarded = [self::ID];

    protected $casts = [
        self::TAGS => 'array',
        self::IS_PINNED => 'boolean',
    ];
}
