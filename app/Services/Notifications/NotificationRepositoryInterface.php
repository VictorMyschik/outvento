<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Notification\UserNotificationSetting;
use App\Models\NotificationToken;
use App\Models\User;
use App\Services\Notifications\Enum\EventType;

interface NotificationRepositoryInterface
{
    public function deleteAllUserSettings(int $userId): void;

    public function insertUserSettings(array $data): void;

    public function saveUserSetting(int $id, array $data): int;

    public function deleteUserSetting(int $id): void;

    public function getUserNotificationSettingById(int $id): ?UserNotificationSetting;

    public function getUserNotificationSettingsList(int $userId, ?int $eventTypeId = null): array;

    /**
     * @return User[]
     */
    public function getSubscriptionUsersList(EventType $type): array;

    public function createNewsSubscriptionNotification(array $dto): int;

    public function getNotificationTokenById(int $id): NotificationToken;

    public function getNotificationTypesForUser(User $user): array;
}
