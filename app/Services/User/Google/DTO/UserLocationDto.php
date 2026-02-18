<?php

declare(strict_types=1);

namespace App\Services\User\Google\DTO;

final readonly class UserLocationDto
{
    public function __construct(
        public string $placeId,
        public float $lat,
        public float $lng,
        public string $countryCode,
        public ?string $cityName,
    ) {}
}