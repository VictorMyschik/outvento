<?php

declare(strict_types=1);

namespace App\Repositories\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use App\Models\Travel\TravelPoint;
use App\Models\Travel\TravelResource;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Travel\DTO\TravelPointDto;
use App\Services\Travel\Enum\TravelPointType;
use App\Services\Travel\Enum\TravelResourceType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisible;
use App\Services\Travel\Enum\UserTravelRole;
use App\Services\Travel\TravelRepositoryInterface;
use DateTime;
use RuntimeException;

readonly class TravelRepository extends DatabaseRepository implements TravelRepositoryInterface
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
        return User::join(UIT::getTableName(), 'users.id', '=', UIT::getTableName() . '.user_id')
            ->where('travel_id', $travel->id())
            ->selectRaw('users.*, ' . UIT::getTableName() . '.role')->get()->all();
    }

    /**
     * @return Travel[]
     */
    public function getPublicList(?User $user, array $filter = []): array
    {
        if (!$user) {
            $query = Travel::where('visible_type', TravelVisible::Public)->whereIn('status', [TravelStatus::Active]);
        }

        if ($user) {
            $query = Travel::whereIn('status', [TravelStatus::Active, TravelStatus::Archived]);
        }

        // Filtering
        if (!empty($filter['country'])) {
            $query->where('country_id', (int)$filter['country']);
        }

        if (!empty($filter['travelType'])) {
            $query->where('travel_type_id', (int)$filter['travelType']);
        }

        if (!empty($filter['dateFrom'])) {
            $dateFrom = DateTime::createFromFormat('Y-m-d', $filter['dateFrom']);
            $query->where('date_from', '>=', $dateFrom->format('Y-m-d'));
        }

        if (!empty($filter['dateTo'])) {
            $dateTo = DateTime::createFromFormat('Y-m-d', $filter['dateTo']);
            $query->where('date_to', '<=', $dateTo->format('Y-m-d'));
        }

        if (!empty($filter['maxMemberFrom'])) {
            $query->where('members', '>=', (int)$filter['maxMemberFrom']);
        }

        if (!empty($filter['maxMemberTo'])) {
            $query->where('members', '<=', (int)$filter['maxMemberTo']);
        }

        if (!empty($filter['freeMember'])) {
            // members-members_exists

            $query->whereRaw($this->db->raw('members - members_exists >= ' . (int)$filter['freeMember']));

        }

        if (!empty($filter['limit'])) {
            $query->limit((int)$filter['limit']);
        }

        return $query->get()->all();
    }

    public function getTravelFullImages(int $travelId): array
    {
        return TravelMedia::where('travel_id', $travelId)->get()->all();
    }

    public function getTravelById(int $travelId): ?Travel
    {
        return Travel::find($travelId);
    }

    public function getTravelLogo(int $travelId): ?TravelMedia
    {
        return TravelMedia::where('travel_id', $travelId)->where('is_avatar', true)->first();
    }

    public function getTravelMediaList(int $travelId): array
    {
        return $this->db->table(TravelMedia::getTableName())->where('travel_id', $travelId)
            ->orderBy('is_avatar', 'DESC')
            ->orderBy('sort', 'ASC')
            ->get()->all();
    }

    #region Images
    public function saveImage(int $id, array $input): int
    {
        if ($id > 0) {
            $this->db->table(TravelMedia::getTableName())->where('id', $id)->update($input);
            return $id;
        }

        return $this->db->table(TravelMedia::getTableName())->insertGetId($input);
    }

    public function deleteTravelMedia(int $mediaId): void
    {
        $this->db->table(TravelMedia::getTableName())->where('id', $mediaId)->delete();
    }

    public function getTravelMedia(int $mediaId): TravelMedia
    {
        return TravelMedia::loadByOrDie($mediaId);
    }

    #endregion
    public function saveTravelUser(int $userId, int $travelId, UserTravelRole $role): void
    {
        $this->db->table(UIT::getTableName())->updateOrInsert([
            'user_id'   => $userId,
            'travel_id' => $travelId,
        ], [
            'role' => $role->value,
        ]);
    }

    public function updateTravelCountries(int $travelId, array $countryIds): void
    {
        $this->db->table('travel_countries')->where('travel_id', $travelId)->delete();

        $insertData = [];
        foreach ($countryIds as $countryId) {
            $insertData[] = [
                'travel_id'  => $travelId,
                'country_id' => $countryId,
            ];
        }

        if (!empty($insertData)) {
            $this->db->table('travel_countries')->insert($insertData);
        }
    }

    public function updateTravelActivities(int $travelId, array $activityIds): void
    {
        $this->db->table('travel_activities')->where('travel_id', $travelId)->delete();

        $insertData = [];
        foreach ($activityIds as $activityId) {
            $insertData[] = [
                'travel_id' => $travelId,
                'activity'  => $activityId,
            ];
        }

        if (!empty($insertData)) {
            $this->db->table('travel_activities')->insert($insertData);
        }
    }

    public function deleteTravel(int $travelId): void
    {
        $this->db->table(Travel::getTableName())->where('id', $travelId)->delete();
    }

    public function setAsLogo(int $imageId): void
    {
        $image = TravelMedia::loadByOrDie($imageId);
        $this->db->beginTransaction();
        $this->db->table(TravelMedia::getTableName())->where('travel_id', $image->travel_id)->update(['is_avatar' => false]);
        $this->db->table(TravelMedia::getTableName())->where('id', $imageId)->update(['is_avatar' => true]);
        $this->db->commit();
    }

    public function getTravelMediaSize(int $travelId): int
    {
        return (int)TravelMedia::where('travel_id', $travelId)->sum('size');
    }

    public function getFullUserMediaSize(int $userId): int
    {
        return (int)TravelMedia::join(Travel::getTableName(), TravelMedia::getTableName() . '.travel_id', '=', Travel::getTableName() . '.id')
            ->join(UIT::getTableName(), UIT::getTableName() . '.travel_id', '=', Travel::getTableName() . '.id')
            ->where('user_id', $userId)->sum(TravelMedia::getTableName() . '.size');
    }

    public function savePoint(int $pointId, TravelPointType $type, TravelPointDto $data): int
    {
        $table = $this->db->table(TravelPoint::getTableName());

        $payload = [
            'city_id'     => $data->cityId,
            'rating'      => $data->rating,
            'type'        => $type->value,
            'point'       => $this->db->raw(
                sprintf(
                    'ST_SetSRID(ST_MakePoint(%F, %F), 4326)::geography',
                    $data->lng,
                    $data->lat,
                )
            ),
            'address'     => $data->address,
            'description' => $data->description,
            'position'    => $data->position,
        ];

        if ($pointId) {
            $updated = $table
                ->where('id', $pointId)
                ->where('travel_id', $data->travelId)
                ->update($payload);

            if ($updated === 0) {
                throw new RuntimeException('Travel point not found or access denied');
            }

            return $pointId;
        }

        $payload['travel_id'] = $data->travelId;

        return $table->insertGetId($payload);
    }

    public function getTravelPoints(int $travelId): array
    {
        return TravelPoint::where('travel_id', $travelId)
            ->orderBy('position', 'ASC')
            ->get()->all();
    }

    public function deletePoint(int $pointId): void
    {
        $this->db->table(TravelPoint::getTableName())->where('id', $pointId)->delete();
    }

    public function deleteTravelPoints(int $travelId): void
    {
        $this->db->table(TravelPoint::getTableName())->where('travel_id', $travelId)->delete();
    }

    public function getTravelLinks(int $travelId): array
    {
        return $this->db->table(TravelResource::getTableName())
            ->orderBy('sort', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->where('travel_id', $travelId)
            ->where('type', TravelResourceType::Link->value)
            ->get()->all();
    }

    public function getResources(int $travelId): array
    {
        return TravelResource::where('travel_id', $travelId)
            ->orderBy('sort', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->get()->all();
    }

    public function saveTravelResource(int $resourceId, array $data): int
    {
        if ($resourceId > 0) {
            $this->db->table(TravelResource::getTableName())->where('id', $resourceId)->update($data);
            return $resourceId;
        }

        return $this->db->table(TravelResource::getTableName())->insertGetId($data);
    }

    public function deleteTravelResource(int $resourceId): void
    {
        $this->db->table(TravelResource::getTableName())->where('id', $resourceId)->delete();
    }

    public function deleteTravelResources(int $travelId): void
    {
        $this->db->table(TravelResource::getTableName())->where('travel_id', $travelId)->delete();
    }

    public function getTravelResourcesSize(int $travelId): int
    {
        return (int)$this->db->table(TravelResource::getTableName())->where('travel_id', $travelId)->sum('size');
    }

    public function deleteTravelMedias(int $travelId): void
    {
        $this->db->table(TravelMedia::getTableName())->where('travel_id', $travelId)->delete();
    }
}
