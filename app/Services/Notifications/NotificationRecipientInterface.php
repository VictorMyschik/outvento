<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Enum\EventType;

interface NotificationRecipientInterface
{
    public function notify(mixed $instance);

    public function getUnsubscribeToken(EventType $type): string;

    public function notificationChannelsFor(string $notificationClass): array;

    public function routeNotificationForMail($notification = null): string;
}
