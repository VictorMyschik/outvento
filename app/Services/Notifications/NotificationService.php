<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Notification\UserNotificationSetting;
use App\Notifications\NewsNotification;
use App\Repositories\System\SettingsRepositoryInterface;
use App\Services\Notifications\Enum\NotificationType;
use App\Services\Subscription\SubscriptionRepositoryInterface;

final readonly class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $repository,
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SettingsRepositoryInterface     $settingsRepository,
    ) {}

    public function saveUserSetting(int $id, array $data): int
    {
        return $this->repository->saveUserSetting($id, $data);
    }

    public function getUserNotificationSettingById(int $id): ?UserNotificationSetting
    {
        return $this->repository->getUserNotificationSettingById($id);
    }

    public function deleteUserSetting(int $id): void
    {
        $this->repository->deleteUserSetting($id);
    }

    public function getSubscribersList(NotificationType $type): array
    {
        return array_merge(
            $this->repository->getSubscriptionUsersList($type),
            $this->subscriptionRepository->getListByType($type),
        );
    }

    public function isNotificationEnabled(): bool
    {
        return $this->settingsRepository->notificationEnabled();
    }

    public function sendNewsNotification(NotificationRecipientInterface $recipient, array $newsList): void
    {


        $recipient->notify(new NewsNotification($newsList));
    }
}