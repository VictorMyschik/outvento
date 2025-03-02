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

    const CONTINENT_UNKNOWN = 0;
    const CONTINENT_AF = 1;
    const CONTINENT_AS = 2;
    const CONTINENT_EU = 3;
    const CONTINENT_NA = 4;
    const CONTINENT_OC = 5;
    const CONTINENT_SA = 6;
    const CONTINENT_AN = 7;

    protected static array $continents = array(
        self::CONTINENT_AF => 'Africa',
        self::CONTINENT_AS => 'Asia',
        self::CONTINENT_EU => 'Europe',
        self::CONTINENT_NA => 'North America',
        self::CONTINENT_OC => 'Oceania',
        self::CONTINENT_SA => 'South America',
        self::CONTINENT_AN => 'Antarctica',
    );

    protected static array $continent_short = array(
        self::CONTINENT_AF => 'AF',
        self::CONTINENT_AS => 'AS',
        self::CONTINENT_EU => 'EU',
        self::CONTINENT_NA => 'NA',
        self::CONTINENT_OC => 'OC',
        self::CONTINENT_SA => 'SA',
        self::CONTINENT_AN => 'AN',
    );

    public static function getContinentList(): array
    {
        return self::$continents;
    }

    public static function getContinentShortList(): array
    {
        return self::$continent_short;
    }

    public function getContinent(): int
    {
        return $this->continent;
    }

    public function getContinentName(): string
    {
        return self::$continents[$this->getContinent()];
    }

    public function getContinentShortName(): string
    {
        return self::$continent_short[$this->getContinent()];
    }

    public function setContinent(int $value): void
    {
        $this->continent = $value;
    }

    public function getISO3166alpha2(): string
    {
        return $this->iso3166alpha2;
    }

    public function setISO3166alpha2(string $value): void
    {
        $this->iso3166alpha2 = $value;
    }

    public function getISO3166alpha3(): string
    {
        return $this->iso3166alpha3;
    }

    public function setISO3166alpha3(string $value): void
    {
        $this->iso3166alpha3 = $value;
    }

    public function getISO3166numeric(): string
    {
        return $this->iso3166numeric;
    }

    public function setISO3166numeric(string $value): void
    {
        $this->iso3166numeric = $value;
    }

    public function getCodeWithName(): string
    {
        $r = $this->getISO3166alpha2();
        $r .= ' ' . $this->getName();

        return $r;
    }

    public function getCodeWithTitleName(): string
    {
        $title = $this->getName();

        return "<span title={$title}>{$this->getISO3166alpha2()}</span>";
    }

    public function getCountryDisplay(): string
    {
        $title = $this->getName();

        $r = "<span title='{$title}'>";
        $r .= $this->getISO3166alpha3();
        $r .= "</span>";
        return $r;
    }
}
