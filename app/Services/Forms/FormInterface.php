<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Services\Forms\Enum\FormType;

interface FormInterface
{
    public function getType(): FormType;

    public function jsonSerialize(): array;
}
