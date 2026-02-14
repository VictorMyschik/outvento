<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;

final readonly class ServiceNotificationService extends AbstractNotificationService
{
    public function resetToDefault(int $userId): void
    {
        $this->repository->purgeServiceUserNotifications($userId);
    }

    public function getServiceNotificationList(int $userId, ?int $eventTypeId = null): array
    {
        return $this->repository->getUserNotificationSettingsList($userId, $eventTypeId);
    }

    public function isUserNotificationActive(int $userId, ServiceEvent $event): bool
    {
        return $this->repository->isUserNotificationActive($userId, $event);
    }

    public function deleteUserSettingByEventAndChannel(int $userId, ServiceEvent $event, NotificationChannel $channel): void
    {
        $this->repository->deleteUserSettingByEventAndChannel($userId, $event, $channel);
    }

    public function updateNotificationMute(int $userId, ServiceEvent $event, bool $active): void
    {
        $active ? $this->repository->unmuteUserNotification($userId, $event) : $this->repository->muteUserNotification($userId, $event);
    }

    public function updateUserServiceNotification(int $userId, ServiceEvent $event, ServiceNotificationDto $dto): void
    {
        if (!$dto->communicationId) {
            $this->deleteUserSettingByEventAndChannel($userId, $event, $dto->channel);

            return;
        }

        $list = $this->getServiceNotificationList($userId, $event->value);

        foreach ($list as $item) {
            if ($item->channel !== $dto->channel->value || $item->event !== $event->value) {
                if ((int)$item->communication_id === $dto->communicationId) {
                    return;
                }

                $this->deleteUserSettingByEventAndChannel($userId, $event, $dto->channel);
            }
        }

        $this->saveUserSetting(0, [
            'user_id'          => $userId,
            'event'            => $event->value,
            'communication_id' => $dto->communicationId,
            'channel'          => $dto->channel->value,
        ]);
    }
}