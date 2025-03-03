<?php

declare(strict_types=1);

namespace App\Services\References\API;

use App\Http\Controllers\Reference\Response\Components\CountryComponent;
use App\Http\Controllers\Reference\Response\Components\TravelTypeComponent;
use App\Http\Controllers\Reference\Response\FullReferenceResponse;
use App\Services\References\ReferenceRepositoryInterface;
use App\Services\System\Enum\Language;

final readonly class ReferenceApiService
{
    public function __construct(
        private ReferenceRepositoryInterface $repository,
    ) {}

    public function getFullReference(Language $language): FullReferenceResponse
    {
        return new FullReferenceResponse(
            countries: new CountryComponent(
                title: __('mr-t.country'), options: $this->getUsingCountrySelectList($language)
            ),
            travelTypes: new TravelTypeComponent(
                title: __('mr-t.travel_type'), options: $this->getTravelTypes($language)
            )
        );
    }

    public function getTravelTypes(Language $language): array
    {
        return $this->repository->getTravelTypeSelectList($language);
    }

    public function getCountrySelectList(Language $language): array
    {
        return $this->repository->getCountrySelectList($language);
    }

    public function getUsingCountrySelectList(Language $language): array
    {
        return $this->repository->getUsingCountrySelectList($language);
    }
}
