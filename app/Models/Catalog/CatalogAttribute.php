<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\Lego\Fields\SortFieldTrait;
use App\Models\ORM\ORM;

class CatalogAttribute extends ORM
{
    use NameFieldTrait;
    use SortFieldTrait;
    use DescriptionNullableFieldTrait;

    protected $table = 'catalog_attributes';
    public $timestamps = false;
    protected $fillable = [
        'group_attribute_id',
        'name',
        'description',
        'sort',
    ];

    protected $casts = [
        'id'                 => 'int',
        'group_attribute_id' => 'int',
        'name'               => 'string',
        'description'        => 'string',
        'sort'               => 'int',
    ];

    public function getGroupAttributeId(): int
    {
        return $this->group_attribute_id;
    }
}
