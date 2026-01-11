<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Services\Notifications\Enum\NotificationType;
use App\Services\System\Enum\Language;

interface FormInterface
{
    public function getLanguage(): Language;

    public function getType(): NotificationType;

    public function getJson(): string;
}
