<?php

declare(strict_types=1);

namespace App\Models\Lego\Fields;

trait TitleFieldTrait
{
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $value): void
    {
        $this->title = $value;
    }
}
