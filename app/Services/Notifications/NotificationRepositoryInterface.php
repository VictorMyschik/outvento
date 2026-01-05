<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Notification\UserNotificationSetting;

interface NotificationRepositoryInterface
{
    public function saveUserSetting(int $id, array $data): int;

    public function deleteUserSetting(int $id): void;

    public function getUserNotificationSettingById(int $id): ?UserNotificationSetting;
}