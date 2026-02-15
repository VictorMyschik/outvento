<?php

declare(strict_types=1);

namespace App\Services\Promo\DTO;

use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Promo\Enum\PromoSource;
use App\Services\System\Enum\Language;

final readonly class SubscriptionDto
{
    public function __construct(
        public string      $email,
        public Language    $language,
        public PromoEvent  $event,
        public PromoSource $source,
    ) {}
}