<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class CatalogAttribute extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;
    use SortFieldTrait;
    use DescriptionNullableFieldTrait;

    protected $table = 'catalog_attributes';
    public $timestamps = false;
    protected $fillable = [
        'group_attribute_id',
        'name',
        'description',
    ];

    protected $casts = [
        'id'                 => 'int',
        'group_attribute_id' => 'int',
        'name'               => 'string',
        'description'        => 'string',
    ];
}
