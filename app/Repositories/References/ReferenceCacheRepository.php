<?php

declare(strict_types=1);

namespace App\Repositories\References;

use App\Services\References\ReferenceRepositoryInterface;
use App\Services\System\Enum\Language;
use Psr\SimpleCache\CacheInterface;

final readonly class ReferenceCacheRepository implements ReferenceRepositoryInterface
{
    private const string COUNTRIES = 'countries';
    private const string COUNTRIES_USING = 'countries_using';
    private const string TRAVEL_TYPES = 'travel_types';
    private const string CITIES = 'cities';

    public function __construct(
        private ReferenceRepositoryInterface $repository,
        private CacheInterface               $cache
    ) {}

    public function getCountrySelectList(Language $language): array
    {
        return $this->cache->rememberForever(self::COUNTRIES . $language->getCode(), function () use ($language) {
            return $this->repository->getCountrySelectList($language);
        });
    }

    public function getUsingCountrySelectList(Language $language): array
    {
        return $this->cache->rememberForever(self::COUNTRIES_USING . $language->getCode(), function () use ($language) {
            return $this->repository->getUsingCountrySelectList($language);
        });
    }

    public function getTravelTypeSelectList(Language $language): array
    {
        return $this->cache->rememberForever(self::TRAVEL_TYPES . $language->getCode(), function () use ($language) {
            return $this->repository->getTravelTypeSelectList($language);
        });
    }

    public function saveTravelType(int $id, array $data): int
    {
        $this->repository->saveTravelType($id, $data);

        $this->flush(self::TRAVEL_TYPES);

        return $id;
    }

    private function flush(string $key): void
    {
        foreach (Language::list() as $language) {
            $this->cache->forget($key . $language->getCode());
        }
    }

    public function saveCity(int $id, array $data): int
    {
        $this->repository->saveCity($id, $data);

        $this->flush(self::CITIES);

        return $id;
    }
}
