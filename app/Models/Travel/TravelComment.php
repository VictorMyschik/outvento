<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;

class TravelComment extends ORM
{
    protected $table = 'travel_comments';

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}