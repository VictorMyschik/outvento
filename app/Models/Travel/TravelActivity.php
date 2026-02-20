<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;

class TravelActivity extends ORM
{
    public $timestamps = false;
    protected $table = 'travel_activities';
}