<?php

declare(strict_types=1);

namespace App\Services\Notifications\DTO;

final readonly class SubscriptionDto
{
    public function __construct(
        public string $email,
        public int    $language,
        public string $token,
        public string $type,
    ) {}
}
