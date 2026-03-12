<?php

declare(strict_types=1);

namespace App\Services\Notifications;

final readonly class InternalNotificationService extends AbstractNotificationService
{
    public function saveUserNotification(int $id, array $data): void
    {
        $this->repository->saveUserNotification($id, $data);
    }

    public function getNotificationList(int $userId): array
    {
        return $this->repository->getInternalNotificationsByUserId($userId);
    }

    public function send(int $userId, string $title, string $message): void
    {
        $this->repository->addInternalUserNotification([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
        ]);
    }

    public function deleteNotificationById(int $userId, int $notificationId): void
    {
        $this->repository->deleteInternalNotificationById($userId, $notificationId);
    }

    public function markUsRead(int $notificationId): void
    {
        $this->repository->updateInternalNotification($notificationId, ['read_at' => now()]);
    }

    public function purgeInternalUserNotifications(int $userId): void
    {
        $this->repository->purgeInternalUserNotifications($userId);
    }

    public function markAllAsRead(int $userId): void
    {
        $this->repository->markAllInternalNotificationsAsRead($userId);
    }
}