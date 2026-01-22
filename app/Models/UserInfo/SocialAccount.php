<?php

declare(strict_types=1);

namespace App\Models\UserInfo;

use App\Models\ORM\ORM;
use App\Models\User;

class SocialAccount extends ORM
{
    public const null UPDATED_AT = null;

    protected $fillable = [
        'provider',
        'provider_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}