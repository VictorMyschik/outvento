<?php

declare(strict_types=1);

namespace App\Services\Language\API;

use App\Services\Language\API\Response\TranslateResponse;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Language\TranslateRepositoryInterface;
use App\Services\System\Enum\Language;

final readonly class TranslateApiService
{
    public function __construct(private TranslateRepositoryInterface $repository) {}

    public function getTranslateFor(TranslateGroupEnum $group, Language $language): TranslateResponse
    {
        return new TranslateResponse(
            translations: $this->repository->getTranslateForGroup($group, $language),
        );
    }
}