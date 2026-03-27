<?php

namespace App\Services\Notifications;

use Illuminate\Notifications\Notification;

interface NotificationRecipientInterface
{
    public function notify(mixed $instance);

    public function preferredLocale(): string;

    public function getUnsubscribeToken(): string;

    public function notificationChannelsFor(string $notificationClass): array;

    public function routeNotificationForMail(Notification $notification): string|array|null;

    public function routeNotificationForTelegram(Notification $notification): string|int|null;

    public function routeNotificationForInternal(Notification $notification): int|string|null;
}
