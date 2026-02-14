<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Services\Notifications\AbstractNotificationService;
use App\Services\Notifications\Enum\PromoEvent;

final readonly class NewsletterDispatchService
{
    public function __construct(
        private NewsRepositoryInterface     $repository,
        private AbstractNotificationService $notificationService,
    ) {}

    public function runDispatch(): void
    {
        // TODO: убрать notification service - это отдельный домен
       /* if (!$this->notificationService->isNotificationEnabled()) {
            return;
        }

        $recipients = $this->notificationService->getSubscribersList(PromoEvent::News);

        $newsList = $this->repository->getTodayNewsList();

        if (empty($newsList)) {
            return;
        }

        foreach ($recipients as $recipient) {
            $this->notificationService->sendNewsNotification($recipient, $newsList);
        }*/
    }
}
