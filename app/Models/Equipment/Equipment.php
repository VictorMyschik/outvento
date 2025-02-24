<?php

namespace App\Models\Equipment;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use App\Models\Reference\CategoryEquipment;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Equipment extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;
    use DescriptionNullableFieldTrait;

    protected $table = 'equipments';
    public $timestamps = false;

    protected $fillable = array(
        'name',
        'description',
        'category_id',
    );

    protected array $allowedSorts = [
        'id',
        'name',
        'description',
    ];

    public function getCategory(): ?CategoryEquipment
    {
        return CategoryEquipment::loadBy($this->category_id);
    }
}
