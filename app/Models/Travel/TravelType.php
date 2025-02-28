<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TravelType extends ORM
{
    use AsSource;
    use Filterable;

    public $timestamps = false;

    protected $table = 'travel_types';

    protected array $allowedSorts = [
        'name_ru',
        'name_en',
        'name_pl',
    ];

    protected $fillable = [
        'name_ru',
        'name_en',
        'name_pl',
    ];
}
