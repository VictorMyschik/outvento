<?php

declare(strict_types=1);

namespace App\Services\Catalog\API;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final readonly class CatalogAPICache implements CatalogAPIInterface
{
    public const string CACHE_TAG = 'search_goods_';

    public function __construct(private CatalogAPIResponse $repository, private Repository $cache) {}

    public function searchGoods(string $query, int $limit): array
    {
        $key = 'search_' . md5($query) . '_' . $limit;

        return $this->cache->tags([self::CACHE_TAG])->rememberForever($key, function () use ($query, $limit) {
            return $this->repository->searchGoods($query, $limit);
        });
    }

    public static function clearCache(): void
    {
        Cache::tags([self::CACHE_TAG])->flush();
    }
}