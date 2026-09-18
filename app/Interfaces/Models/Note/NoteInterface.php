<?php

namespace App\Interfaces\Models\Note;

use App\Interfaces\Models\BaseModelInterface;
use App\Interfaces\Models\Shared\HasContentInterface;
use App\Interfaces\Models\Shared\HasIdInterface;
use App\Interfaces\Models\Shared\HasIsPinnedInterface;
use App\Interfaces\Models\Shared\HasTagsInterface;
use App\Interfaces\Models\Shared\HasTitleInterface;
use App\Interfaces\Models\Shared\HasUuidInterface;

interface NoteInterface extends
    BaseModelInterface,
    HasContentInterface,
    HasIdInterface,
    HasIsPinnedInterface,
    HasTagsInterface,
    HasTitleInterface,
    HasUuidInterface
{
    const TABLE = 'notes';

    const TITLE_MAX_LENGTH = 255;

    const TAG_MAX_LENGTH = 32;
}
