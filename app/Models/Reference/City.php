<?php

declare(strict_types=1);

namespace App\Models\Reference;

use App\Models\ORM\ORM;
use App\Services\System\Enum\Language;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class City extends ORM
{
    use AsSource;
    use Filterable;

    public const null UPDATED_AT = null;

    protected $table = 'cities';

    protected $fillable = [
        'country_id',
        'name',
        'timezone',
        'lat',
        'lng',
        'place_id',
        'point',
    ];

    protected array $allowedSorts = [
        'id',
        'country_id',
        'name',
        'timezone',
        'lat',
        'lng',
        'place_id',
        'point',
        'created_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
    ];

    public function getCountry(): Country
    {
        return Country::loadByOrDie($this->country_id);
    }

    public function getName(Language $language): string
    {
        return CityTranslation::where('city_id', $this->id)->where('language', $language->value)->value('name');
    }
}
