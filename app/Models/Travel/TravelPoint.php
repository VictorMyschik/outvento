<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;

class TravelPoint extends ORM
{
    protected $table = 'travel_points';

    public $fillable = [
        'travel_id',
        'city_id',
        'type',
        'position',
        'address',
        'description',
        'point',
    ];
}