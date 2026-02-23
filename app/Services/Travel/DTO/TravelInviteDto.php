<?php

declare(strict_types=1);

namespace App\Services\Travel\DTO;

final readonly class TravelInviteDto
{
    public function __construct(
        public array  $activities,
        public array  $countryLabels,
        public string $confirmationUrl,
    ) {}
}