<?php

declare(strict_types=1);

namespace App\Services\Travel\DTO;

final class TravelPointDto
{
    public int $cityId;

    public function __construct(
        public int     $travelId,
        public string  $address,
        public int     $position,
        public int     $rating,
        public float   $lat,
        public float   $lng,
        public ?string $description,
    ) {}

    public function setCityId(int $cityId): void
    {
        $this->cityId = $cityId;
    }
}