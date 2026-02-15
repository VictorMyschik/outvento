<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\UserInfo\Communication;
use App\Notifications\NewsNotification;
use App\Repositories\System\SettingsRepositoryInterface;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Resolvers\CommunicationChannelSupportResolver;

abstract readonly class AbstractNotificationService
{
    public const int EXPIRE_MINUTES = 20;

    public function __construct(
        protected NotificationRepositoryInterface $repository,
        protected SettingsRepositoryInterface     $settingsRepository,
    ) {}

    public static function getUnsubscribeUrl(string $token): string
    {
        return config('app.front_host') . '/unsubscribe?token=' . $token;
    }

    public static function getConfirmUrl(string $token): string
    {
        return config('app.front_host') . '/confirm?token=' . $token;
    }

    public function saveUserSetting(int $id, array $data): int
    {
        $communication = Communication::loadByOrDie((int)$data['communication_id']);
        $data['channel'] = CommunicationChannelSupportResolver::fromCommunicationType($communication->getType())->value;

        return $this->repository->saveServiceUserNotification($id, $data);
    }

    public function deleteUserSetting(int $id): void
    {
        $this->repository->deleteUserSetting($id);
    }

    public function getAuthSubscribersList(ServiceEvent $type): array
    {
        return $this->repository->getSubscriptionUsersList($type);
    }

    public function isNotificationEnabled(): bool
    {
        return $this->settingsRepository->notificationEnabled();
    }
}
