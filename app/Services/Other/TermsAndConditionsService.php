<?php

declare(strict_types=1);

namespace App\Services\Other;

use App\Repositories\Other\TermsAndConditionsRepository;
use App\Services\System\Enum\Language;

final readonly class TermsAndConditionsService
{
    public function __construct(
        private TermsAndConditionsRepository $repository,
    ) {}

    public function createTermsAndCondition(Language $language): int
    {
        return $this->repository->createTermsAndCondition($language);
    }

    public function deleteTermsAndCondition(int $id): void
    {
        $this->repository->deleteTermsAndCondition($id);
    }

    public function saveTermsAndCondition(int $id, array $data): void
    {
        $this->repository->saveTermsAndCondition($id, $data);
    }

    public function clone(int $id): int
    {
        return $this->repository->clone($id);
    }
}