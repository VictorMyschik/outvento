<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCode extends Model
{
    protected $table = 'notification_codes';
    public const string ACTION_VERIFY_REG = 'verify-registration';
    public const null UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'code',
        'action',
        'data',
    ];
}
