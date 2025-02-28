<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Models\Reference\Country;
use App\Repositories\DatabaseRepository;
use App\Services\References\CountryRepositoryInterface;
use App\Services\System\Enum\Language;

class CountryRepository extends DatabaseRepository implements CountryRepositoryInterface
{
    public function getSelectList(Language $language): array
    {
        $field = 'name_' . $language->getCode();

        return $this->db->table(Country::getTableName())
            ->orderBy($field)->pluck($field, 'id')
            ->toArray();
    }
}
