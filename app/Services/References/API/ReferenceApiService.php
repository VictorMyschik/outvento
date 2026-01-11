<?php

declare(strict_types=1);

namespace App\Services\References\API;

use App\Services\References\API\Response\Components\CountryComponent;
use App\Services\References\ReferenceRepositoryInterface;
use App\Services\System\Enum\Language;
use App\Services\Travel\Api\Components\TravelTypeComponent;

final readonly class ReferenceApiService
{
    public function __construct(
        private ReferenceRepositoryInterface $repository,
    ) {}

    public function getTravelTypeList(Language $language): array
    {
        foreach ($this->repository->getTravelTypeList() as $type) {
            $out[] = new TravelTypeComponent(
                id: $type->id(),
                name: $type->getName($language),
                icon: $type->getImageUrl(),
            );
        }

        return $out ?? [];
    }

    public function getCountrySelectList(Language $language): array
    {
        return $this->buildCountryResponse($this->repository->getCountrySelectList($language));
    }

    public function getUsingCountrySelectList(Language $language): array
    {
        return $this->buildCountryResponse($this->repository->getUsingCountrySelectList($language));
    }

    private function buildCountryResponse(array $list): array
    {
        $out = [];
        foreach ($list as $country) {
            $out[] = new CountryComponent(
                id: $country->id,
                iso2: $country->iso2,
                label: $country->name,
            );
        }

        return $out;
    }
}
