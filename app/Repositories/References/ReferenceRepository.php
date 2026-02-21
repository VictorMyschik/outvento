<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Models\ModelRole;
use App\Models\ORM\ORM;
use App\Models\Reference\City;
use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\Activity;
use App\Models\UserInfo\CommunicationType;
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
        return Activity::all();
    }

    public function saveTravelType(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Activity::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(Activity::getTableName())->insertGetId($data);
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

    public function saveCommunicationType(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(CommunicationType::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(CommunicationType::getTableName())->insertGetId($data);
    }

    /**
     * @param class-string<ORM> $class
     */
    public function save(int $id, string $class, array $data): int
    {
        if ($id > 0) {
            if (isset($data['roles'])) {
                $this->db->table(ModelRole::getTableName())->where([
                    'table_name' => $class,
                    'model_id'   => $id,
                ])->delete();

                $modelRoleData = [];
                foreach ($data['roles'] as $roleId) {
                    $modelRoleData[] = [
                        'table_name' => $class,
                        'model_id'   => $id,
                        'role_id'    => $roleId,
                    ];
                }

                $this->db->table(ModelRole::getTableName())->insert($modelRoleData);
            }

            unset($data['roles']);

            $this->db->table($class::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        $id = $this->db->table($class::getTableName())->insertGetId($data);

        if (isset($data['roles'])) {
            $modelRoleData = [];

            foreach ($data['roles'] as $roleId) {
                $modelRoleData[] = [
                    'table_name' => $class,
                    'model_id'   => $id,
                    'role_id'    => $roleId,
                ];
            }
            unset($data['roles']);
            $this->db->table(ModelRole::getTableName())->insert($modelRoleData);
        }

        $this->db->table($class::getTableName())->where('id', $id)->update($data);

        return $id;
    }

    public function getRolesByModel(ORM $class): array
    {
        return $this->db->table(ModelRole::getTableName())
            ->join('roles', ModelRole::getTableName() . '.role_id', '=', 'roles.id')
            ->where(ModelRole::getTableName() . '.table_name', $class::class)
            ->where(ModelRole::getTableName() . '.model_id', $class->id)
            ->pluck('name', 'roles.id')->all();
    }
}
