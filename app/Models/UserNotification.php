<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ORM\ORM;

class UserNotification extends ORM
{
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'message',
        'read_at',
    ];

    public $casts = [
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function getUser(): User
    {
        return User::findOrFail((int)$this->user_id);
    }
}