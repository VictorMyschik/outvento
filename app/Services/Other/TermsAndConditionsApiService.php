<?php

declare(strict_types=1);

namespace App\Services\Other;

use App\Repositories\Other\TermsAndConditionsRepository;
use App\Services\Other\Response\TermsAndConditionResponse;
use App\Services\System\Enum\Language;

final readonly class TermsAndConditionsApiService
{
    public function __construct(
        private TermsAndConditionsRepository $repository,
    ) {}

    public function getTermsAndConditions(Language $language): TermsAndConditionResponse
    {
        $terms = $this->repository->getByLanguage($language, now());
        $publishedAt = $terms->published_at ?: $terms->created_at;

        return new TermsAndConditionResponse(
            text: $terms->text,
            publishedAt: $publishedAt,
        );
    }
}