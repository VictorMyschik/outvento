<?php

declare(strict_types=1);

namespace App\Models\Lego\Fields;

trait CodeFieldTrait
{
    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $value): void
    {
        $this->code = $value;
    }
}
