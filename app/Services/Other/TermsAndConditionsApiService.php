<?php

declare(strict_types=1);

namespace App\Services\Other;

use App\Http\Controllers\API\Response\Common\TermsAndConditionsResponse;
use App\Repositories\Other\TermsAndConditionsRepository;
use App\Services\System\Enum\Language;

final readonly class TermsAndConditionsApiService
{
    public function __construct(
        private TermsAndConditionsRepository $repository,
    ) {}

    public function getTermsAndConditions(Language $language): TermsAndConditionsResponse
    {
        $terms = $this->repository->getByLanguage($language, now());
        $publishedAt = $terms->published_at ?: $terms->created_at;

        return new TermsAndConditionsResponse(
            text: $terms->text,
            publishedAt: $publishedAt,
        );
    }
}