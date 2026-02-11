<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use App\Repositories\User\UserRepository;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\NotificationService;
use App\Services\User\Enum\CommunicationTypeCode;
use App\Services\User\Enum\VerificationStatus;
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
        if (CommunicationType::loadByOrDie($data['type_id'])->getCode() === CommunicationTypeCode::Mail) {
            if (!filter_var($data['address'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email address');
            }

            // From admin only
            $status = VerificationStatus::from((int)($data['verification_status'] ?? VerificationStatus::NotVerified->value));

            if ($status->isVerified()) {
                return $this->repository->saveCommunication($id, $data);
            }

            $this->verificationEmail($id, $data);
        }

        return $this->repository->saveCommunication($id, $data);
    }

    public function sendCommunicationVerifyEmail(Communication $communication): void
    {
        $this->notificationService->addAndSendRequest(
            channel: NotificationChannel::Email,
            address: $communication->address,
            eventType: EventType::VerifyCommunicationEmail,
            data: $communication->toArray(),
        );
    }

    private function verificationEmail(int $id, array $data): void
    {
        if (!$id) {
            $this->notificationService->addAndSendRequest(
                channel: NotificationChannel::Email,
                address: $data['address'],
                eventType: EventType::VerifyCommunicationEmail,
                data: $data,
            );

            return;
        }

        $current = $this->repository->getCommunicationById($id, (int)$data['user_id']);

        if ($current->address !== $data['address']) {
            $this->notificationService->addAndSendRequest(
                channel: NotificationChannel::Email,
                address: $data['address'],
                eventType: EventType::VerifyCommunicationEmail,
                data: $data,
            );
        }
    }

    public function getCommunications(int $userId): array
    {
        return $this->repository->getCommunications($userId);
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
