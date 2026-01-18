<?php

namespace App\Services\Notifications;

interface NotificationRecipientInterface
{
    public function notify(mixed $instance);

    public function getUnsubscribeToken(): string;

    public function notificationChannelsFor(string $notificationClass): array;

    public function routeNotificationForMail($notification = null): string;
}
