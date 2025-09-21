<?php

namespace App\Orchid\Filters\Catalog;

use App\Models\Catalog\CatalogAttributeValue;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class CatalogAttributeValueFilter extends Filter
{
    public static function runQuery(int $attributeId)
    {
        return CatalogAttributeValue::filters([self::class])->where('catalog_attribute_id', $attributeId)->orderBy('id')->get();
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }
}
