<?php

namespace App\Interfaces\Models;

use App\Interfaces\Models\Shared\HasIdInterface;

interface BaseModelInterface extends HasIdInterface
{
    const DELETED_AT = 'deleted_at';

    public function getResourceClass(): array|string;

    public function countLoaded(string $relationship): bool;
}
