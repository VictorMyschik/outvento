<?php

declare(strict_types=1);

namespace App\Repositories\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\UIT;
use App\Repositories\DatabaseRepository;

class TravelRepository extends DatabaseRepository implements TravelRepositoryInterface
{
    public function saveTravel(int $travelId, array $data): int
    {
        if ($travelId > 0) {
            return $this->db->table(Travel::getTableName())->where('id', $travelId)->update($data);
        }

        return $this->db->table(Travel::getTableName())->insertGetId($data);
    }

    public function getTravelUsers(Travel $travel): array
    {
        return UIT::where('travel_id', $travel->id())->get()->all();
    }
}
