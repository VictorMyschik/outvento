<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Services\References\CountryRepositoryInterface;
use App\Services\System\Enum\Language;
use Psr\SimpleCache\CacheInterface;

final readonly class CountryCacheRepository implements CountryRepositoryInterface
{
    private const string COUNTRIES = 'countries';

    public function __construct(
        private CountryRepositoryInterface $repository,
        private CacheInterface             $cache
    ) {}

    public function getSelectList(Language $language): array
    {
        return $this->cache->rememberForever(self::COUNTRIES . $language->getCode(), function () use ($language) {
            return $this->repository->getSelectList($language);
        });
    }
}
