<?php

namespace App\Models;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TravelType extends ORM
{
    use AsSource;
    use Filterable;

    use NameFieldTrait;
    use DescriptionNullableFieldTrait;

    public $timestamps = false;

    protected $table = 'travel_types';

    protected array $allowedSorts = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'description',
    ];
}
