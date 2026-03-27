<?php

declare(strict_types=1);

namespace App\Services\Location;

use App\Models\Reference\City;
use App\Services\User\Google\DTO\CityLocationDto;
use App\Services\User\GoogleApiInterface;

final readonly class LocationService
{
    public function __construct(
        private GoogleApiInterface          $google,
        private LocationRepositoryInterface $repository,
    ) {}

    public function getCityExt(CityLocationDto $dto): City
    {
        $city = $this->repository->getCityByPlaceId($dto->placeId);

        if (!$city) {
            $timezone = $this->google->getTimezoneByCoordinates($dto->lat, $dto->lng);
            $city = $this->repository->createCity($dto, $timezone);
        } else {
            $exists = $this->repository->getCityTranslate($city->id, $dto->language);

            if (!$exists) {
                $this->repository->createCityTranslate($city->id, $dto->language, $dto->cityName);
            }
        }

        return $city;
    }

    public function getCityByPlaceId() {}

    public function createCity() {}
}