<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Notifications\PromoNotificationService;
use App\Services\Promo\SubscriptionService;

final readonly class NewsletterDispatchService
{
    public function __construct(
        private NewsRepositoryInterface  $repository,
        private PromoNotificationService $notificationService,
        private SubscriptionService      $subscriptionService,
    ) {}

    public function runDispatch(): void
    {
        if (!$this->notificationService->isNotificationEnabled()) {
            return;
        }

        $recipients = $this->subscriptionService->getSubscribersList(PromoEvent::News);

        $newsList = $this->repository->getTodayNewsList();

        if (empty($newsList)) {
            return;
        }

        foreach ($recipients as $recipient) {
            $this->notificationService->sendNewsNotification($recipient, $newsList);
        }
    }
}
