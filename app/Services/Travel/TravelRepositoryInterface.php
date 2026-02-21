<?php

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\User;
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


    public function getTravelLogo(int $travelId): ?TravelImage;

    public function getTravelImage(int $imageId): ?TravelImage;

    /**
     * @return TravelImage[]
     */
    public function getTravelPhotoList(int $travelId): array;

    public function saveImage(int $id, array $input): int;

    public function deleteTravelImage(int $imageId): void;

    public function deleteTravel(int $travelId): void;
}
