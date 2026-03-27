<?php

declare(strict_types=1);

namespace App\Services\Notifications\Enum;

enum NotificationAudience: string
{
    case User = 'user';
    case Support = 'support';
    case Admin = 'admin';
}
