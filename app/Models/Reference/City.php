<?php

declare(strict_types=1);

namespace App\Models\Reference;

use App\Models\Lego\Fields\NameByLanguageFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class City extends ORM
{
    protected $table = 'cities';

    use AsSource;
    use Filterable;
    use NameByLanguageFieldTrait;

    public function getCountry(): Country
    {
        return Country::loadByOrDie($this->country_id);
    }
}
