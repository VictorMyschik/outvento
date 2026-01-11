<?php

namespace App\Models\Lego\Fields;

use Illuminate\Support\Facades\Blade;

trait ActiveFieldTrait
{
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $value): void
    {
        $this->active = $value;
    }

    public function isActiveDisplay(): string
    {
        return Blade::render($this->isActive() ? '<x-orchid-icon class="text-success" path="check" />' : '<x-orchid-icon class="text-danger" path="ban" />');
    }
}
