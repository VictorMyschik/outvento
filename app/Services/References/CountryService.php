<?php

declare(strict_types=1);

namespace App\Services\References;

use App\Services\System\Enum\Language;

readonly class CountryService
{
    public function __construct(
        private CountryRepositoryInterface $repository,
    ) {}

    public function getSelectList(Language $language): array
    {
        return $this->repository->getSelectList($language);
    }
}
