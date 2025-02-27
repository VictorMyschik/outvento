<?php

declare(strict_types=1);

namespace App\Http\Controllers\Travel\Response;

use App\Http\Controllers\Response\CountryResponse;
use App\Http\Controllers\Response\TravelTypeResponse;
use App\Http\Controllers\Travel\Response\Components\TravelStatusComponent;
use App\Http\Controllers\Travel\Response\Components\TravelUserComponent;
use App\Http\Controllers\Travel\Response\Components\TravelVisibleType;

final readonly class TravelDetailsResponse
{
    public function __construct(
        public int                   $id,
        public string                $title,
        public string                $description,
        public TravelStatusComponent $status,
        public TravelVisibleType     $visible_kind,
        public TravelUserComponent   $user,
        public CountryResponse       $country,
        public TravelTypeResponse    $travel_type,
        public string                $created_at,
        public ?string               $updated_at,
        public array                 $images, // TravelImageComponent[]
    ) {}
}
