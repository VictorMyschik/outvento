<?php

declare(strict_types=1);

namespace App\Services\User\Google\Response;

final readonly class TimeZoneResponse
{
    public function __construct(
        public float  $dstOffset,
        public float  $rawOffset,
        public string $status,
        public string $timeZoneId,
        public string $timeZoneName,
    ) {}
}