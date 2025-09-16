<?php

declare(strict_types=1);

namespace App\Services\Catalog\API;

interface CatalogAPIInterface
{
    public function searchGoods(string $query, int $limit): array;
}