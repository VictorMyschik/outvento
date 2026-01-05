<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Notification\UserNotificationSetting;

final readonly class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $repository,
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
}