<?php

namespace App\Models\Reference;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class CategoryEquipment extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;
    use DescriptionNullableFieldTrait;

    public $timestamps = false;

    protected $table = 'category_equipments';

    protected $fillable = array(
        'id',
        'name',
        'description',
    );

    protected array $allowedSorts = [
        'id',
        'name',
        'description',
    ];
}
