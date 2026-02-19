<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\Reference\City;
use App\Models\Reference\Country;
use App\Models\Reference\UserLocation;
use App\Repositories\DatabaseRepository;
use App\Services\User\Google\DTO\UserLocationDto;
use DomainException;

final readonly class UserLocationRepository extends DatabaseRepository
{
    public function getCityByPlaceId(string $placeId): ?City
    {
        return City::where('place_id', $placeId)->first();
    }

    public function createCity(UserLocationDto $dto, string $timezone): City
    {
        $countryId = $this->db->table(Country::getTableName())->where('iso3166alpha2', $dto->countryCode)->value('id');

        if (!$countryId) {
            throw new DomainException(
                "Country with code {$dto->countryCode} not found"
            );
        }

        $id = $this->db->table(City::getTableName())->insertGetId([
            'place_id'   => $dto->placeId,
            'country_id' => $countryId,
            'timezone'   => $timezone,
            'name'       => $dto->cityName ?? 'Unknown',
            'lat'        => $dto->lat,
            'lng'        => $dto->lng,
        ]);

        return City::loadByOrDie($id);
    }

    public function setLocation(int $userId, int $cityId, float $lat, float $lng): void
    {
        $location = UserLocation::updateOrCreate([
            'user_id' => $userId
        ], [
            'city_id' => $cityId,
            'lat'     => $lat,
            'lng'     => $lng,
        ]);

        $this->db->statement(
            '
                UPDATE user_locations
                SET geom = ST_SetSRID(
                    ST_MakePoint(?, ?),
                    4326
                )::geography
                WHERE id = ?
                ',
            [$lng, $lat, $location->id]
        );
    }

    public function deleteUserLocation(int $userId): void
    {
        $this->db->table(UserLocation::getTableName())->where('user_id', $userId)->delete();
    }
}