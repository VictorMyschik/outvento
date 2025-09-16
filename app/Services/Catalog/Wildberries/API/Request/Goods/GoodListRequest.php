<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Request\Goods;

use App\Services\Catalog\Wildberries\API\Request\Goods\Components\SettingsComponent;

final readonly class GoodListRequest
{
    public function __construct(
        public SettingsComponent $settings
    ) {}
}
