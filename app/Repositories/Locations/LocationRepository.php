<?php

declare(strict_types=1);

namespace App\Repositories\Locations;

use App\Models\Reference\City;
use App\Models\Reference\CityTranslation;
use App\Models\Reference\Country;
use App\Repositories\DatabaseRepository;
use App\Services\Location\LocationRepositoryInterface;
use App\Services\System\Enum\Language;
use App\Services\User\Google\DTO\CityLocationDto;
use DomainException;

final readonly class LocationRepository extends DatabaseRepository implements LocationRepositoryInterface
{
    public function getCityByPlaceId(string $placeId): ?City
    {
        return City::where('place_id', $placeId)->first();
    }

    public function getCityTranslate(int $cityId, Language $language): ?string
    {
        return $this->db->table(CityTranslation::getTableName())
            ->where('city_id', $cityId)
            ->where('language', $language->value)
            ->value('name');
    }

    public function createCityTranslate(int $cityId, Language $language, string $name): void
    {
        $this->db->table(CityTranslation::getTableName())->insert([
            'city_id'  => $cityId,
            'language' => $language->value,
            'name'     => $name,
        ]);
    }

    public function createCity(CityLocationDto $dto, string $timezone): City
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
            'lat'        => $dto->lat,
            'lng'        => $dto->lng,
        ]);

        $this->db->table(CityTranslation::getTableName())->insert([
            'city_id'  => $id,
            'language' => $dto->language->value,
            'name'     => $dto->cityName ?? 'Unknown',
        ]);

        return City::loadByOrDie($id);
    }
}