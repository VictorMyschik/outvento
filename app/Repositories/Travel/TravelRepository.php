<?php

declare(strict_types=1);

namespace App\Repositories\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\Travel\TravelType;
use App\Models\Travel\UIT;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Travel\Enum\ImageType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisibleType;
use App\Services\Travel\TravelRepositoryInterface;

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
        return UIT::where('travel_id', $travel->id())->get()->all();
    }

    /**
     * @return Travel[]
     */
    public function getPublicList(?User $user, array $filter = []): array
    {
        if (!$user) {
            $query = Travel::where('visible_type', TravelVisibleType::VISIBLE_TYPE_PUBLIC)->whereIn('status', [TravelStatus::STATUS_ACTIVE]);
        }

        if ($user) {
            $query = Travel::whereIn('status', [TravelStatus::STATUS_ACTIVE, TravelStatus::STATUS_ARCHIVED]);
        }

        // Filtering
        if (!empty($filter['country'])) {
            $query->where('country_id', (int)$filter['country']);
        }

        if (!empty($filter['travelType'])) {
            $query->where('travel_type_id', (int)$filter['travelType']);
        }

        if (!empty($filter['dateFrom'])) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d', $filter['dateFrom']);
            $query->where('date_from', '>=', $dateFrom->format('Y-m-d'));
        }

        if (!empty($filter['dateTo'])) {
            $dateTo = \DateTime::createFromFormat('Y-m-d', $filter['dateTo']);
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
        return TravelImage::where('travel_id', $travelId)->get()->all();
    }

    public function getTravelById(int $travelId): ?Travel
    {
        return Travel::find($travelId);
    }

    public function getTravelTypeList(): array
    {
        return TravelType::get()->all();
    }

    public function getTravelLogo(int $travelId): ?TravelImage
    {
        return TravelImage::where('travel_id', $travelId)->where('type', ImageType::LOGO->value)->first();
    }

    public function getTravelPhotoList(int $travelId): array
    {
        return TravelImage::where('travel_id', $travelId)->where('type', ImageType::PHOTO->value)->get()->all();
    }

    #region Images
    public function saveImage(int $id, array $input): int
    {
        if ($id > 0) {
            $this->db->table(TravelImage::getTableName())->where('id', $id)->update($input);
            return $id;
        }

        return $this->db->table(TravelImage::getTableName())->insertGetId($input);
    }

    public function deleteTravelImage(int $imageId): void
    {
        $this->db->table(TravelImage::getTableName())->where('id', $imageId)->delete();
    }

    public function getTravelImage(int $imageId): ?TravelImage
    {
        return TravelImage::find($imageId);
    }

    #endregion
}
