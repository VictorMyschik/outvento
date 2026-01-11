<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Services\Notifications\Enum\NotificationType;
use App\Services\Notifications\NotificationService;

final readonly class NewsletterDispatchService
{
    public function __construct(
        private NewsRepositoryInterface $repository,
        private NotificationService     $notificationService,
    ) {}

    public function runDispatch(): void
    {
        if (!$this->notificationService->isNotificationEnabled()) {
            return;
        }

        $recipients = $this->notificationService->getSubscribersList(NotificationType::News);

        $newsList = $this->repository->getTodayNewsList();

        if (empty($newsList)) {
            return;
        }

        foreach ($recipients as $recipient) {
            $this->notificationService->sendNewsNotification($recipient, $newsList);
        }
    }
}