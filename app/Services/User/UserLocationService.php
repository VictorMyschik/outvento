<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Repositories\User\UserLocationRepository;
use App\Services\User\Google\DTO\UserLocationDto;

final readonly class UserLocationService
{
    public function __construct(
        private UserLocationRepository $repository,
        private GoogleApiInterface     $google,
    ) {}

    public function saveUserLocation(int $userId, UserLocationDto $dto): void
    {
        $city = $this->repository->getCityByPlaceId($dto->placeId);

        if (!$city) {
            $timezone = $this->google->getTimezoneByCoordinates($dto->lat, $dto->lng);
            $city = $this->repository->createCity($dto, $timezone);
        }

        $this->repository->setLocation($userId, $city->id, $dto->lat, $dto->lng);
    }

    public function deleteUserLocation(int $userId): void
    {
        $this->repository->deleteUserLocation($userId);
    }
}