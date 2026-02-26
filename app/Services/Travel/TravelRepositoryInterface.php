<?php

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use App\Models\User;
use App\Services\Travel\DTO\TravelPointDto;
use App\Services\Travel\Enum\TravelPointType;
use App\Services\Travel\Enum\UserTravelRole;

interface TravelRepositoryInterface
{
    public function saveTravelUser(int $userId, int $travelId, UserTravelRole $role): void;

    public function saveTravel(int $travelId, array $data): int;

    public function updateTravelCountries(int $travelId, array $countryIds): void;

    public function updateTravelActivities(int $travelId, array $activityIds): void;

    public function getTravelUsers(Travel $travel): array;

    public function getPublicList(?User $user, array $filter = []): array;

    public function getTravelFullImages(int $travelId): array;

    public function getTravelById(int $travelId): ?Travel;


    public function getTravelLogo(int $travelId): ?TravelMedia;

    public function getTravelMedia(int $mediaId): TravelMedia;

    /**
     * @return TravelMedia[]
     */
    public function getTravelMediaList(int $travelId): array;

    public function saveImage(int $id, array $input): int;

    public function deleteTravelMedia(int $mediaId): void;

    public function deleteTravel(int $travelId): void;

    public function setAsLogo(int $imageId): void;

    public function getFullTravelMediaSize(int $travelId): int;

    public function getTravelPoints(int $travelId): array;

    public function savePoint(int $pointId, TravelPointType $type, TravelPointDto $data): int;

    public function deletePoint(int $pointId): void;

    public function deleteTravelPoints(int $travelId): void;
}
