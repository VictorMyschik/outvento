<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Models\Reference\Country;
use App\Repositories\DatabaseRepository;
use App\Services\References\CountryRepositoryInterface;

class CountryRepository extends DatabaseRepository implements CountryRepositoryInterface
{
    public function getSelectList(): array
    {
        return $this->db->table(Country::getTableName())->orderBy('name')->pluck('name', 'id')->toArray();
    }
}
