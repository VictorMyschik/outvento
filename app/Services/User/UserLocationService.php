<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Repositories\User\UserLocationRepository;
use App\Services\Location\LocationService;
use App\Services\User\Google\DTO\CityLocationDto;

final readonly class UserLocationService
{
    public function __construct(
        private UserLocationRepository $repository,
        private LocationService        $location,
    ) {}

    public function saveUserLocation(int $userId, CityLocationDto $dto): void
    {
        $city = $this->location->getCityExt($dto);

        $this->repository->setLocation($userId, $city->id, $dto->lat, $dto->lng);
    }

    public function deleteUserLocation(int $userId): void
    {
        $this->repository->deleteUserLocation($userId);
    }
}