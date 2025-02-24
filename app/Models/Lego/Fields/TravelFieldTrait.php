<?php

namespace App\Models\Lego\Fields;

use App\Models\Travel;

trait TravelFieldTrait
{
    public function getTravel(): Travel
    {
        return Travel::findOrFail($this->travel_id);
    }

    public function setTravelID(int $value): void
    {
        $this->travel_id = $value;
    }
}
