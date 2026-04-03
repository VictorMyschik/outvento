<?php

declare(strict_types=1);

namespace App\Support\Validation;

use Illuminate\Validation\Rules\Password;

final class PasswordRules
{
    public static function default(): Password
    {
        return Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols();
    }
}