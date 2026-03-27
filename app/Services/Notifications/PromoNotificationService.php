<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Notifications\NewsNotification;

final readonly class PromoNotificationService extends AbstractNotificationService
{
    public function sendNewsNotification(NotificationRecipientInterface $recipient, array $newsList): void
    {
        $unsubscribeUrl = self::getUnsubscribeUrl($recipient->getUnsubscribeToken());

        $recipient->notify(new NewsNotification($newsList, $unsubscribeUrl));
    }
}