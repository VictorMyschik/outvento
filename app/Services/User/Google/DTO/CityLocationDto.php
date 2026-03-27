<?php

declare(strict_types=1);

namespace App\Services\User\Google\DTO;

use App\Services\System\Enum\Language;

final readonly class CityLocationDto
{
    public function __construct(
        public string   $placeId,
        public float    $lat,
        public float    $lng,
        public string   $countryCode,
        public ?string  $cityName,
        public Language $language,
    ) {}
}