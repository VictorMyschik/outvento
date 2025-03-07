<?php

declare(strict_types=1);

namespace App\Services\Subscription\DTO;

final readonly class SubscriptionDto
{
    public function __construct(
        public string $email,
        public int    $language,
        public string $token,
        public int    $type,
    ) {}
}
