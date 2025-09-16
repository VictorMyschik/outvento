<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Request\Goods\Components;

final readonly class CursorComponent
{
    public function __construct(
        public string $updatedAt,
        public int    $nmID,
        public int    $limit,
    ) {}
}
