<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
}
