<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Services\Forms\Enum\FormTypeEnum;
use App\Services\System\Enum\Language;

interface FormInterface
{
    public function getLanguage(): Language;

    public function getType(): FormTypeEnum;

    public function getJson(): string;
}
