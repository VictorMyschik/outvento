<?php

declare(strict_types=1);

namespace App\Services\References;

readonly class CountryService
{
    public function __construct(
        private CountryRepositoryInterface $repository,
    ) {}

    public function getSelectList(): array
    {
        return $this->repository->getSelectList();
    }
}
