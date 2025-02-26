<?php

namespace App\Services\Travel;

use App\Models\Travel\Travel;

interface TravelRepositoryInterface
{
    public function saveTravel(int $travelId, array $data): int;

    public function getTravelUsers(Travel $travel): array;
}
