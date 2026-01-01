<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';
    public const string ACTION_VERIFY_REG = 'verify-registration';
    public const null UPDATED_AT = null;

    protected $fillable = [
        'email',
        'token',
    ];
}
