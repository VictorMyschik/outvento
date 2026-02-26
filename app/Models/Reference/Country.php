<?php

declare(strict_types=1);

namespace App\Models\Reference;

use App\Models\Lego\Fields\NameByLanguageFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * Данные берутся https://www.geonames.org/countries/
 */
class Country extends ORM
{
    use AsSource;
    use Filterable;
    use NameByLanguageFieldTrait;

    public $timestamps = false;

    protected $table = 'countries';

    protected array $allowedSorts = [
        'id',
        'continent',
        'name_ru',
        'name_en',
        'name_pl',
        'iso3166alpha2',
        'iso3166alpha3',
        'iso3166numeric',
    ];

    protected $fillable = array(
        'name_ru',
        'name_en',
        'name_pl',
        'iso3166alpha2',
        'iso3166alpha3',
        'iso3166numeric',
        'continent',
    );

    const int CONTINENT_AF = 1;
    const int CONTINENT_AS = 2;
    const int CONTINENT_EU = 3;
    const int CONTINENT_NA = 4;
    const int CONTINENT_OC = 5;
    const int CONTINENT_SA = 6;
    const int CONTINENT_AN = 7;

    public const array CONTINENTS = array(
        self::CONTINENT_AF => 'Africa',
        self::CONTINENT_AS => 'Asia',
        self::CONTINENT_EU => 'Europe',
        self::CONTINENT_NA => 'North America',
        self::CONTINENT_OC => 'Oceania',
        self::CONTINENT_SA => 'South America',
        self::CONTINENT_AN => 'Antarctica',
    );

    public function getContinentName(): string
    {
        return self::CONTINENTS[$this->continent];
    }

    public function getContinentShortName(): string
    {
        return [
            self::CONTINENT_AF => 'AF',
            self::CONTINENT_AS => 'AS',
            self::CONTINENT_EU => 'EU',
            self::CONTINENT_NA => 'NA',
            self::CONTINENT_OC => 'OC',
            self::CONTINENT_SA => 'SA',
            self::CONTINENT_AN => 'AN',
        ][$this->continent];
    }

    public function getCode(): string
    {
        return $this->iso3166alpha2;
    }
}
