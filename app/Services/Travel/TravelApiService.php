<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Http\Controllers\Response\Components\CountryContinentComponent;
use App\Http\Controllers\Response\CountryResponse;
use App\Http\Controllers\Response\TravelTypeResponse;
use App\Http\Controllers\Travel\Response\Components\TravelImageComponent;
use App\Http\Controllers\Travel\Response\Components\TravelStatusComponent;
use App\Http\Controllers\Travel\Response\Components\TravelUserComponent;
use App\Http\Controllers\Travel\Response\Components\TravelVisibleType;
use App\Http\Controllers\Travel\Response\TravelDetailsResponse;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelImage;
use App\Models\User;
use App\Services\Travel\Enum\ImageType;

final readonly class TravelApiService
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository,
    ) {}

    public function getPublicTravelList(?User $user): array
    {
        $out = [];

        foreach ($this->travelRepository->getPublicList($user) as $travel) {
            $out[] = $this->getTravelDetailsResponse($travel);
        }

        return $out;
    }

    public function getPersonalList(User $user): array
    {
        $out = [];
        foreach ($this->getTravelByUserId($user->id()) as $travel) {
            $out[] = $this->getTravelDetailsResponse($travel->id);
        }

        return $out;
    }

    public function getTravelDetailsResponse(Travel $travel): TravelDetailsResponse
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
            description: $travel->getDescription(),
            status: new TravelStatusComponent(
                key: $travel->getStatus()->value,
                name: $travel->getStatus()->getLabel(),
            ),
            visible_kind: new TravelVisibleType(
                key: $travel->getVisibleType()->value,
                name: $travel->getVisibleType()->getLabel(),
            ),
            user: new TravelUserComponent(
                name: $user->name,
                email: $user->email,
            ),
            country: new CountryResponse(
                id: $travel->getCountry()->id(),
                name: $travel->getCountry()->getName(),
                continent: new CountryContinentComponent(
                    name: $travel->getCountry()->getContinentName(),
                    short_name: $travel->getCountry()->getContinentShortName(),
                ),
            ),
            travel_type: new TravelTypeResponse(
                id: $travel->getTravelType()->id(),
                name: $travel->getTravelType()->getName(),
                description: $travel->getTravelType()->getDescription(),
            ),
            created_at: $travel->created_at->toAtomString(),
            updated_at: $travel->updated_at?->toAtomString(),
            images: $images,
        );
    }
}
