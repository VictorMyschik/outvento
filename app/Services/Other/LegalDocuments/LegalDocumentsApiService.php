<?php

declare(strict_types=1);

namespace App\Services\Other\LegalDocuments;

use App\Http\Controllers\API\Response\Common\TermsAndConditionsResponse;
use App\Repositories\Other\LegalDocuments\LegalDocumentRepository;
use App\Services\Other\LegalDocuments\Enum\LegalDocumentType;
use App\Services\System\Enum\Language;

final readonly class LegalDocumentsApiService
{
    public function __construct(
        private LegalDocumentRepository $repository,
    ) {}

    public function getLegalDocumentByType(LegalDocumentType $type, Language $language): TermsAndConditionsResponse
    {
        $terms = $this->repository->getByType($type, $language, now());
        $publishedAt = $terms->published_at ?: $terms->created_at;

        return new TermsAndConditionsResponse(
            text: $terms->text,
            publishedAt: $publishedAt,
        );
    }
}