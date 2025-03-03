<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reference\Response;

use App\Http\Controllers\Reference\Response\Components\CountryComponent;
use App\Http\Controllers\Reference\Response\Components\TravelTypeComponent;

final readonly class FullReferenceResponse
{
    public function __construct(
        public CountryComponent    $countries,
        public TravelTypeComponent $travelTypes,
    ) {}
}
