<?php

namespace App\Models\Lego\Fields;

use App\Models\Shop\Marketplace\Wildberries\WBCatalogGood;

trait WBGoodNullableFieldTrait
{
    public function getGood(): ?WBCatalogGood
    {
        return WBCatalogGood::where('nm_id', $this->nm_id)->first();
    }
}
