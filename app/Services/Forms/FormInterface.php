<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Services\Notifications\Enum\EventType;
use App\Services\System\Enum\Language;

interface FormInterface
{
    public function getLanguage(): Language;

    public function getType(): EventType;

    public function getJson(): string;
}
