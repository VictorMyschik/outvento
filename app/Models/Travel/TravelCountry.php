<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;
use App\Models\Reference\Country;

class TravelCountry extends ORM
{
    public $timestamps = false;

    protected $table = 'travel_countries';

    protected $fillable = [
        'travel_id',
        'country_id',
        'sort',
    ];

    public function getCountry(): Country
    {
        return Country::loadByOrDie($this->country_id);
    }

    public function getTravel(): Travel
    {
        return Travel::loadByOrDie($this->travel_id);
    }
}