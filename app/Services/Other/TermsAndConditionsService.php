<?php

declare(strict_types=1);

namespace App\Services\Other;

use App\Repositories\Other\LegalDocumentRepository;
use App\Services\Other\Enum\LegalDocumentType;
use App\Services\System\Enum\Language;

final readonly class TermsAndConditionsService
{
    public function __construct(
        private LegalDocumentRepository $repository,
    ) {}

    public function createLegalDocument(LegalDocumentType $type, Language $language): int
    {
        return $this->repository->createLegalDocument($type, $language);
    }

    public function deleteLegalDocument(int $id): void
    {
        $this->repository->deleteLegalDocument($id);
    }

    public function saveLegalDocument(int $id, array $data): void
    {
        $this->repository->saveLegalDocument($id, $data);
    }

    public function clone(int $id): int
    {
        return $this->repository->clone($id);
    }
}