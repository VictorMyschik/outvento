<?php

declare(strict_types=1);

namespace App\Services\Catalog\API\DTO;

final readonly class SearchDTO
{
    public function __construct(
        public string $search,
        public int    $page = 0,
        public int    $perPage = 10,
        public string $sort = 'id',
    ) {}
}