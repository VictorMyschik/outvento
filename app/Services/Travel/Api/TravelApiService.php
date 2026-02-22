<?php

declare(strict_types=1);

namespace App\Services\Travel\Api;

use App\Http\Controllers\API\Travel\Response\Components\MembersComponent;
use App\Http\Controllers\API\Travel\Response\Components\TravelMediaComponent;
use App\Http\Controllers\API\Travel\Response\Components\TravelStatusComponent;
use App\Http\Controllers\API\Travel\Response\Components\TravelUserComponent;
use App\Http\Controllers\API\Travel\Response\Components\TravelVisibleType;
use App\Http\Controllers\API\Travel\Response\TravelDetailsResponse;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelMedia;
use App\Models\User;
use App\Services\References\API\Response\Components\CountryComponent;
use App\Services\System\Enum\Language;
use App\Services\Travel\Api\Components\TravelListByTypeComponent;
use App\Services\Travel\Api\Components\TravelTypeComponent;
use App\Services\Travel\Enum\Activity;
use App\Services\Travel\Enum\ImageType;
use App\Services\Travel\TravelRepositoryInterface;

final readonly class TravelApiService
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository,
    ) {}

    public function travelExamples(Language $language): array
    {
        $items = [];

        foreach (Activity::getSelectList() as $item) {
            $items[] = new TravelListByTypeComponent(
                travelTypeId: $item->value,
                travels: $this->searchTravels([
                    'travelType' => $item->value,
                    'limit'      => 3,
                    'dateFrom'   => now()->subYear()->toDateString(),
                ], $language, null)
            );
        }

        return $items;
    }


    private function buildTravelTypeComponent(Activity $travelType, Language $language): TravelTypeComponent
    {
        return new TravelTypeComponent(
            id: $travelType->id(),
            name: $travelType->getName($language),
            icon: $travelType->getImageUrl(),
        );
    }

    public function searchTravels(array $input, Language $language, ?User $user): array
    {
        $out = [];

        if (empty($input['dateFrom'])) {
            $input['dateFrom'] = now()->toDateString();
        }

        foreach ($this->travelRepository->getPublicList($user, $input) as $travel) {
            $out[] = $this->getTravelDetailsResponse($travel, $language);
        }

        return $out;
    }

    public function getPublicTravelList(?User $user, Language $language): array
    {
        $out = [];

        foreach ($this->travelRepository->getPublicList($user) as $travel) {
            $out[] = $this->getTravelDetailsResponse($travel, $language);
        }

        return $out;
    }

    public function getPersonalList(User $user, Language $language): array
    {
        $out = [];
        foreach ($this->getTravelByUserId($user->id()) as $travel) {
            $out[] = $this->getTravelDetailsResponse($travel->id, $language);
        }

        return $out;
    }

    public function getTravelDetailsResponse(Travel $travel, Language $language): TravelDetailsResponse
    {
        $images = [];

        /** @var TravelMedia $image */
        foreach ($this->travelRepository->getTravelFullImages($travel->id()) as $image) {
            $images[] = new TravelMediaComponent(
                logo: $image->getType() === ImageType::LOGO,
                url: $image->getUrl(),
                description: $image->getDescription(),
            );
        }

        $user = $travel->getUser();

        return new TravelDetailsResponse(
            id: $travel->id(),
            title: $travel->getTitle(),
            preview: $travel->getPreview(),
            description: $travel->getDescription(),
            status: new TravelStatusComponent(
                key: $travel->getStatus()->value,
                name: $travel->getStatus()->getLabel(),
            ),
            visibleType: new TravelVisibleType(
                key: $travel->getVisibleType()->value,
                name: $travel->getVisibleType()->getLabel(),
            ),
            user: new TravelUserComponent(
                name: $user->name,
                email: $user->email,
            ),
            country: new CountryComponent(
                id: 0,
                iso2: '',
                label: '',
            ),
            travelType: $this->buildTravelTypeComponent($travel->getActivities(), $language),
            dateFrom: $travel->getDateFrom()->format('d.M.Y'),
            dateTo: $travel->getDateTo()->format('d.M.Y'),
            members: new MembersComponent(
                maxMember: $travel->getMaxMembers(),
                existsMembers: $travel->getMembers(),
                title: __('mr-t.travel_members'),
            ),
            images: $images,
            owner: $travel->getUser()->name,
        );
    }
}
