<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCode extends Model
{
    public const string ACTION_VERIFY_REG = 'verify-registration';
    public const string ACTION_RESET_PASS = 'reset-password';

    public const null UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'code',
        'action',
        'data',
    ];
}
