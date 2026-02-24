<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;
use App\Models\Reference\City;
use App\Services\Travel\Enum\TravelPointType;

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

    public $casts = [
        'position'   => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getTravel(): Travel
    {
        return Travel::loadByOrDie($this->travel_id);
    }

    public function getCity(): City
    {
        return City::loadByOrDie($this->city_id);
    }

    public function gettype(): TravelPointType
    {
        return TravelPointType::from($this->type);
    }
}