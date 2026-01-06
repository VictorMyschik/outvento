<?php

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\Travel\TravelType;
use App\Models\User;

interface TravelRepositoryInterface
{
    public function saveTravel(int $travelId, array $data): int;

    public function getTravelUsers(Travel $travel): array;

    public function getPublicList(?User $user, array $filter = []): array;

    public function getTravelFullImages(int $travelId): array;

    public function getTravelById(int $travelId): ?Travel;

    /**
     * @return TravelType[]
     */
    public function getTravelTypeList(): array;

    public function getTravelLogo(int $travelId): ?TravelImage;

    public function getTravelImage(int $imageId): ?TravelImage;

    /**
     * @return TravelImage[]
     */
    public function getTravelPhotoList(int $travelId): array;

    public function saveImage(int $id, array $input): int;

    public function deleteTravelImage(int $imageId): void;
}
