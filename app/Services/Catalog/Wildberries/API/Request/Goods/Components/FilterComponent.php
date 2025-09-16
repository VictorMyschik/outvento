<?php

declare(strict_types=1);

namespace App\Services\Catalog\Wildberries\API\Request\Goods\Components;

final readonly class FilterComponent
{
    public function __construct(
        public string $textSearch,
        public bool   $allowedCategoriesOnly,
        public array  $tagIDs,
        public array  $objectIDs,
        public array  $brands,
        public int    $withPhoto,
    ) {}
}
