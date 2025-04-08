<?php

namespace App\Services\Travel;

use App\Models\Travel\Travel;
use App\Models\User;

interface TravelRepositoryInterface
{
    public function saveTravel(int $travelId, array $data): int;

    public function getTravelUsers(Travel $travel): array;

    public function getPublicList(?User $user, array $filter = []): array;

    public function getTravelFullImages(int $travelId): array;

    public function getTravelById(int $travelId): ?Travel;

    public function getTravelTypeList(): array;
}
