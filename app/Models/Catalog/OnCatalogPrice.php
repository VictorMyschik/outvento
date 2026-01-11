<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Models\ORM\ORM;

class OnCatalogPrice extends ORM
{
    protected $table = 'on_prices';

    protected $casts = [
        'id'         => 'integer',
        'good_id'    => 'integer',
        'market_id'  => 'string', // from onliner.by
        'price'      => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getGoodId(): int
    {
        return $this->good_id;
    }

    public function getMarketId(): string
    {
        return $this->market_id;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
