<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\ORM\ORM;

class CatalogGoodAttribute extends ORM
{
    protected $table = 'good_attributes';

    protected $casts = [
        'good_id'            => 'int',
        'attribute_value_id' => 'int',
        'bool_value'         => 'bool',
    ];

    public function getGoodId(): int
    {
        return $this->good_id;
    }

    public function getAttributeValueId(): int
    {
        return $this->attribute_value_id;
    }

    public function getBoolValue(): ?bool
    {
        return $this->bool_value;
    }
}
