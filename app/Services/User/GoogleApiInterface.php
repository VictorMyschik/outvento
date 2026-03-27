<?php

namespace App\Services\User;

interface GoogleApiInterface
{
    public function getTimezoneByCoordinates(float $lat, float $lng): string;
}