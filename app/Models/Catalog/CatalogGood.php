<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\JsonFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class CatalogGood extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;
    use JsonFieldTrait;
    use DescriptionNullableFieldTrait;

    protected $table = 'catalog_goods';

    protected array $allowedSorts = [
        'id',
        'group_id',
        'prefix',
        'name',
        'manufacturer_id',
        'parent_good_id',
        'int_id',
        'string_id',
        'link',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id'               => 'integer',
        'group_id'         => 'integer',
        'prefix'           => 'string',
        'name'             => 'string',
        'short_info'       => 'string',
        'description'      => 'string',
        'manufacturer_id'  => 'integer',
        'parent_good_id'   => 'string',
        'is_certification' => 'boolean',
        'int_id'           => 'integer',
        'string_id'        => 'string',
        'link'             => 'string',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'sl'             => 'json',
    ];

    public function getGroupID(): int
    {
        return $this->group_id;
    }

    public function getGroup(): CatalogGroup
    {
        return CatalogGroup::loadByOrDie($this->getGroupID());
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getShortInfo(): ?string
    {
        return $this->short_info;
    }

    public function getManufacturerId(): ?int
    {
        return $this->manufacturer_id;
    }

    public function getParentGoodId(): ?string
    {
        return $this->parent_good_id;
    }

    public function isCertification(): bool
    {
        return $this->is_certification;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return Manufacturer::loadBy($this->getManufacturerId());
    }
}
