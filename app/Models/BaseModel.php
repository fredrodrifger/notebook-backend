<?php

namespace App\Models;

use AliMousavi\Filoquent\Traits\Filterable;
use App\Interfaces\Models\BaseModelInterface;
use App\Traits\MagicMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;

abstract class BaseModel extends Model implements BaseModelInterface
{
    use Filterable;
    use HasFactory;
    use MagicMethodsTrait;

    public function getResourceClass(): array|string
    {
        $class = $this->getMorphClass();

        return str_replace('\Models\\', '\Http\Resources\\', $class).'Resource';
    }

    public function countLoaded(string $relationship): bool
    {
        $attribute = (new Stringable($relationship))->snake()->finish('_count')->value();

        return array_key_exists($attribute, $this->getAttributes());
    }
}
