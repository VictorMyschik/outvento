<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Services\References\CountryRepositoryInterface;
use Psr\SimpleCache\CacheInterface;

final readonly class CountryCacheRepository implements CountryRepositoryInterface
{
    private const COUNTRIES = 'countries';

    public function __construct(
        private CountryRepositoryInterface $repository,
        private CacheInterface             $cache
    ) {}

    public function getSelectList(): array
    {
        return $this->cache->rememberForever(self::COUNTRIES, function () {
            return $this->repository->getSelectList();
        });
    }
}
