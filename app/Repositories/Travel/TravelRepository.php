<?php

declare(strict_types=1);

namespace App\Repositories\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisibleType;
use App\Services\Travel\TravelRepositoryInterface;

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

    /**
     * @return Travel[]
     */
    public function getPublicList(?User $user, array $filter = []): array
    {
        if (!$user) {
            $query = Travel::where('visible_type', TravelVisibleType::VISIBLE_TYPE_PUBLIC)->whereIn('status', [TravelStatus::STATUS_ACTIVE, TravelStatus::STATUS_ARCHIVED]);
        }

        if ($user) {
            $query = Travel::whereIn('visible_type', [TravelVisibleType::VISIBLE_TYPE_PUBLIC, TravelVisibleType::VISIBLE_TYPE_PLATFORM])
                ->whereIn('status', [TravelStatus::STATUS_ACTIVE, TravelStatus::STATUS_ARCHIVED]);
        }

        // Filtering

        return $query->get()->all();
    }

    public function getTravelFullImages(int $travelId): array
    {
        return TravelImage::where('travel_id', $travelId)->get()->all();
    }

    public function getTravelById(int $travelId): ?Travel
    {
        return Travel::find($travelId);
    }
}
