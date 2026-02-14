<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\NotificationToken;
use App\Models\User;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;

interface NotificationRepositoryInterface
{
    public function purgeServiceUserNotifications(int $userId): void;

    public function saveServiceUserNotification(int $id, array $data): int;

    public function deleteUserSetting(int $id): void;

    public function getServiceUserNotificationList(int $userId, ?int $eventTypeId = null): array;

    /**
     * @return User[]
     */
    public function getSubscriptionUsersList(ServiceEvent $event): array;

    public function createNewsSubscriptionNotification(array $dto): int;

    public function getNotificationTokenById(int $id): NotificationToken;

    public function isUserNotificationActive(int $userId, ServiceEvent $event): bool;

    public function muteUserNotification(int $userId, ServiceEvent $event): void;

    public function unmuteUserNotification(int $userId, ServiceEvent $event): void;

    public function deleteServiceNotificationsByEventAndChannel(int $userId, ServiceEvent $event, NotificationChannel $channel): void;

    public function deleteServiceNotifications(int $id): void;
}
