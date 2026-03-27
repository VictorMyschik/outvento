<?php

declare(strict_types=1);

namespace App\Services\Promo\Resolvers;

use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Notifications\Enum\SystemEvent;

class SubscriptionNotifyResolver
{
    public static function getSystemEventByPromoEvent(PromoEvent $event): SystemEvent
    {
        return match ($event) {
            PromoEvent::News => SystemEvent::NewNewsSubscription,
            default => throw new \Exception('Event not created'),
        };
    }
}