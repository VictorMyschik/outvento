<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;

class TravelCommentVote extends ORM
{
    protected $table = 'travel_comment_votes';
    public $casts = [
        'created_at' => 'datetime',
    ];
}