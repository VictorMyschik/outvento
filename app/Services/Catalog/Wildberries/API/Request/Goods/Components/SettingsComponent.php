<?php
declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Request\Goods\Components;

final readonly class SettingsComponent
{
    public function __construct(
        public CursorComponent $cursor,
        public FilterComponent $filter,
        public SortComponent   $sort,
    ) {}
}

