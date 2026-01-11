<?php

declare(strict_types=1);

namespace App\Services\Catalog\API;

final readonly class CatalogAPIService
{
    public function __construct(private CatalogAPIInterface $repository) {}

    public function searchGoods(string $query, int $limit): array
    {
        return $this->repository->searchGoods($query, $limit);
    }
}