<?php

namespace App\Http\Requests;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class BaseRequest extends FormRequest
{
    protected array $excluded = [];

    public function authorize(): bool
    {
        return true;
    }

    public function safeValidated(): array
    {
        return Arr::except($this->validated(), $this->excluded);
    }

    /**
     * Hard cap on page size — a client may ask for fewer rows than the default, never more.
     * The studied backend returns the default outright for callers it does not recognise as a user;
     * this application has no authentication at all, so the requested value is clamped instead of
     * ignored, otherwise a client could never render a short first page.
     */
    public function perPage(): int
    {
        $requested = $this->integer(Controller::PER_PAGE, Controller::DEFAULT_PAGE_SIZE);

        return max(1, min($requested, Controller::DEFAULT_PAGE_SIZE));
    }
}
