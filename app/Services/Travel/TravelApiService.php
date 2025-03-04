<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Http\Controllers\Response\Components\CountryContinentComponent;
use App\Http\Controllers\Response\Components\MembersComponent;
use App\Http\Controllers\Response\Components\TravelTypeComponent;
use App\Http\Controllers\Response\CountryResponse;
use App\Http\Controllers\Travel\Response\Components\TravelImageComponent;
use App\Http\Controllers\Travel\Response\Components\TravelStatusComponent;
use App\Http\Controllers\Travel\Response\Components\TravelUserComponent;
use App\Http\Controllers\Travel\Response\Components\TravelVisibleType;
use App\Http\Controllers\Travel\Response\TravelDetailsResponse;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\User;
use App\Services\System\Enum\Language;
use App\Services\Travel\Enum\ImageType;

final readonly class TravelApiService
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository,
    ) {}

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

        /** @var TravelImage $image */
        foreach ($this->travelRepository->getTravelFullImages($travel->id()) as $image) {
            $images[] = new TravelImageComponent(
                logo: $image->getType() === ImageType::LOGO,
                name: $image->getName(),
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
            country: new CountryResponse(
                id: $travel->getCountry()->id(),
                name: $travel->getCountry()->getName($language),
                continent: new CountryContinentComponent(
                    name: $travel->getCountry()->getContinentName(),
                    short_name: $travel->getCountry()->getContinentShortName(),
                ),
            ),
            travelType: new TravelTypeComponent(
                id: $travel->getTravelType()->id(),
                name: $travel->getTravelType()->getName($language),
                icon: $travel->getTravelType()->getImageUrl(),
            ),
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
