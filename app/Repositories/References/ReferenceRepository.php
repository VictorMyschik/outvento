<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Models\Reference\City;
use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelType;
use App\Repositories\DatabaseRepository;
use App\Services\References\ReferenceRepositoryInterface;
use App\Services\System\Enum\Language;
use Brick\Money\ISOCurrencyProvider;
use Illuminate\Support\Collection;

readonly class ReferenceRepository extends DatabaseRepository implements ReferenceRepositoryInterface
{
    public function getCountrySelectList(Language $language): array
    {
        $field = 'name_' . $language->getCode();

        return $this->db->table(Country::getTableName())
            ->orderBy($field)->selectRaw(implode(',', [
                Country::getTableName() . '.id as id',
                Country::getTableName() . '.iso3166alpha2 as iso2',
                Country::getTableName() . '.' . $field . ' AS name',
            ]))
            ->groupBy(Country::getTableName() . '.id')->get()->all();
    }

    public function getUsingCountrySelectList(Language $language): array
    {
        $field = 'name_' . $language->getCode();

        return $this->db->table(Country::getTableName())
            ->join(Travel::getTableName(), Country::getTableName() . '.id', '=', Travel::getTableName() . '.country_id')
            ->orderBy($field)->selectRaw(implode(',', [
                Country::getTableName() . '.id as id',
                Country::getTableName() . '.iso3166alpha2 as iso2',
                Country::getTableName() . '.' . $field . ' AS name',
            ]))
            ->groupBy(Country::getTableName() . '.id')->get()->all();
    }

    public function getTravelTypeList(): Collection
    {
        return TravelType::all();
    }

    public function saveTravelType(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(TravelType::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(TravelType::getTableName())->insertGetId($data);
    }

    public function saveCity(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(City::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(City::getTableName())->insertGetId($data);
    }

    public function getCurrencySelectList(): array
    {
        return ISOCurrencyProvider::getInstance()->getAvailableCurrencies();
    }

    public function saveCountry(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Country::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(Country::getTableName())->insertGetId($data);
    }
}
