<?php

declare(strict_types=1);

namespace App\Services\Travel\Api;

final readonly class TravelExamplesResponse
{
    public function __construct(
        public array $travelsByType, // of TravelListByTypeComponent
    ) {}
}