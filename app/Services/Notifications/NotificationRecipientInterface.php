<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Enum\NotificationType;

interface NotificationRecipientInterface
{
    public function notify(mixed $instance);

    public function getUnsubscribeToken(NotificationType $type): string;

    public function notificationChannelsFor(string $notificationClass): array;

    public function routeNotificationForMail($notification = null): string;
}