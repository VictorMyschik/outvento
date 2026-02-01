<?php

declare(strict_types=1);

namespace App\Services\Other\Faq\Api;

use App\Http\Controllers\API\Response\FaqComponent;
use App\Services\Other\Faq\FaqRepositoryInterface;
use App\Services\System\Enum\Language;

final readonly class FaqApiService
{
    public function __construct(private FaqRepositoryInterface $repository) {}

    public function searchFaqs(string $q, Language $language): array
    {
        $response = [];
        foreach ($this->repository->searchFaqs($q, $language) as $stdClass) {
            $response[] = new FaqComponent(
                title: $stdClass->title,
                text: $stdClass->text
            );
        }

        return $response;
    }

    public function getBaseFaqs(Language $language): array
    {
        $response = [];
        foreach ($this->repository->getBaseFaqs($language) as $stdClass) {
            $response[] = new FaqComponent(
                title: $stdClass->title,
                text: $stdClass->text
            );
        }

        return $response;
    }
}