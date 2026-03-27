<?php

declare(strict_types=1);

namespace App\Models\Reference;

use App\Models\Lego\Fields\LanguageFieldTrait;
use App\Models\ORM\ORM;

class CityTranslation extends ORM
{
    use LanguageFieldTrait;

    protected $table = 'city_translations';

    public function getCity(): City
    {
        return City::loadByOrDie($this->city_id);
    }
}