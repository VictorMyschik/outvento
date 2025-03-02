<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Services\System\Enum\Language;

final readonly class ReferenceService
{
    public function __construct(
        private ReferenceRepositoryInterface $repository,
    ) {}

    public function getCountrySelectList(Language $language): array
    {
        return $this->repository->getCountrySelectList($language);
    }

    public function getTravelTypeSelectList(Language $language): array
    {
        return $this->repository->getTravelTypeSelectList($language);
    }

    public function saveTravelType(int $id, array $data): int
    {
        return $this->repository->saveTravelType($id, $data);
    }

    public function saveCity(int $id, array $data): int
    {
        return $this->repository->saveCity($id, $data);
    }
}
