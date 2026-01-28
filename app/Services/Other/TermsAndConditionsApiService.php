<?php

declare(strict_types=1);

namespace App\Services\Other;

use App\Http\Controllers\API\Response\Common\TermsAndConditionsResponse;
use App\Repositories\Other\LegalDocumentRepository;
use App\Services\Other\Enum\LegalDocumentType;
use App\Services\System\Enum\Language;

final readonly class TermsAndConditionsApiService
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