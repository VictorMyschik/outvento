<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Travel\Response;

use App\Http\Controllers\Response\CountryResponse;
use App\Http\Controllers\Travel\Travel\Response\Components\MembersComponent;
use App\Http\Controllers\Travel\Travel\Response\Components\TravelStatusComponent;
use App\Http\Controllers\Travel\Travel\Response\Components\TravelUserComponent;
use App\Http\Controllers\Travel\Travel\Response\Components\TravelVisibleType;

final readonly class TravelDetailsResponse
{
    public function __construct(
        public int                   $id,
        public string                $title,
        public ?string               $preview,
        public ?string               $description,
        public TravelStatusComponent $status,
        public TravelVisibleType     $visibleType,
        public TravelUserComponent   $user,
        public CountryResponse       $country,
        public TravelTypeComponent   $travelType,
        public string                $dateFrom,
        public ?string               $dateTo,
        public MembersComponent      $members,
        public array                 $images, // TravelImageComponent[]
        public string                $owner,
    ) {}
}
