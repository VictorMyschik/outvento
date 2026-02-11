<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\NotificationService;
use App\Services\System\Enum\Language;
use Illuminate\Http\UploadedFile;

final readonly class UserService
{
    public function __construct(
        private UserUploadService   $uploadService,
        private UserRepository      $repository,
        private AuthService         $authService,
        private NotificationService $notificationService,
    ) {}

    public function updateUser(User $user, array $data): User
    {
        if (count($data)) {
            $this->repository->updateUser($user->id, $data);
            $user->refresh();
        }

        if (!empty($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;

            $this->authService->sendVerifyNotification($user);
        }

        if (count($data)) {
            $this->repository->updateUser($user->id, $data);
        }

        return $this->repository->getUserById($user->id);
    }

    public function updateUserRoles(int $userId, array $roleIds): void
    {
        $this->repository->updateUserRoles($userId, $roleIds);
    }

    public function removeAvatar(User $user): void
    {
        if (!is_null($user->getAvatar())) {
            $this->uploadService->deleteFile($user->avatar);
            $this->repository->deleteAvatar($user->id);
        }
    }

    public function addAvatar(int $userId, UploadedFile $file): void
    {
        $path = $this->uploadService->saveAvatar($userId, $file);
        $this->repository->updateUser($userId, ['avatar' => $path]);
    }

    public function deleteUser(User $user): void
    {
        $this->repository->deleteCommunications($user->id);
        $this->removeAvatar($user);
        $user->tokens()->delete();
        $user->softDelete();

        // TODO: сделать проверку на возможность полного удаления
        //$user->delete();
    }

    public function saveCommunication(int $id, array $data): int
    {
        return $this->repository->saveCommunication($id, $data);
    }

    public function getCommunicationById(int $id, int $userId): ?object
    {
        return $this->repository->getCommunicationById($id, $userId);
    }

    public function getCommunications(int $userId, Language $language): array
    {
        return $this->repository->getCommunications($userId, $language);
    }

    public function deleteCommunication(int $userId, int $id): void
    {
        $this->repository->deleteCommunication($userId, $id);
    }

    public function deleteAllCommunication(int $userId): void
    {
        $this->repository->deleteAllCommunications($userId);
    }

    #region Notifications
    public function getCommunicationChannelTypes(): array
    {
        return $this->repository->getCommunicationChannelTypes(array_keys(NotificationChannel::getSelectList()));
    }

    public function getCommunicationsForNotification(int $userId): array
    {
        return $this->repository->getCommunicationsByTypeCodes($userId, array_keys(NotificationChannel::getSelectList()));
    }

    public function getUserNotificationSettingsList(int $userId): array
    {
        return $this->notificationService->getUserNotificationSettingsList($userId);
    }

    public function resetToDefaultUserNotifications(int $userId): void
    {
        $this->notificationService->resetToDefault($userId);
    }

    public function updateUserNotificationSetting(int $userId, int $eventTypeId, array $data): void
    {
        if (!$eventTypeId) {
            $this->notificationService->updateFullUserNotificationSetting($userId, $data);

            return;
        }

        $list = $this->notificationService->getUserNotificationSettingsList($userId, $eventTypeId);

        foreach ($list as $item) {
            $communicationId = $data[$item->event_type_id][$item->getCommunicationType()->code]['communication_id'] ?? null;
            $active = $data[$item->event_type_id][$item->getCommunicationType()->code]['active'] ?? false;

            if ($communicationId !== null) {
                $this->notificationService->saveUserSetting($item->id, [
                    'active'           => $active,
                    'user_id'          => $userId,
                    'event_type_id'    => $item->event_type_id,
                    'communication_id' => (int)$communicationId,
                ]);
                unset($data[$item->event_type_id][$item->getCommunicationType()->code]);
            } else {
                $this->notificationService->deleteUserSetting($item->id);
            }
        }

        foreach ($data as $eventTypeId => $channels) {
            foreach ($channels as $item) {
                if (empty($item['communication_id'])) {
                    continue;
                }

                $this->notificationService->saveUserSetting(0, [
                    'active'           => $item['active'] ?? false,
                    'user_id'          => $userId,
                    'event_type_id'    => $eventTypeId,
                    'communication_id' => (int)$item['communication_id'],
                ]);
            }
        }
    }

    public function getNotificationTypesForUser(User $user): array
    {
        return $this->notificationService->getNotificationTypesForUser($user);
    }


    #endregion
}
