<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\Notification\ServiceNotification;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Repositories\User\UserRepository;
use App\Services\Notifications\DTO\ServiceNotificationDto;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Enum\SystemEvent;
use App\Services\Notifications\ServiceNotificationService;
use App\Services\Notifications\SystemNotificationService;
use App\Services\User\Enum\CommunicationType;
use App\Services\User\Enum\VerificationStatus;
use Illuminate\Http\UploadedFile;

final readonly class UserService
{
    public function __construct(
        private UserUploadService          $uploadService,
        private UserRepository             $repository,
        private AuthService                $authService,
        private SystemNotificationService  $notificationService,
        private ServiceNotificationService $serviceNotificationService,
    ) {}

    public function updateUser(User $user, array $data): User
    {
        $needSendVerifyEmail = false;

        if (!empty($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
            $needSendVerifyEmail = true;
        }

        if (count($data)) {
            $this->repository->updateUser($user->id, $data);
        }

        if ($needSendVerifyEmail) {
            $this->authService->sendVerifyNotification($user);
        }

        return $this->repository->getUserById($user->id);
    }

    public function updateUserRoles(User $user, array $roleIds): void
    {
        $this->repository->updateUserRoles($user->id, $roleIds);

        $this->serviceNotificationService->deleteUnavailableServiceNotification($user);
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
        $user->tokens()->delete();
        $this->repository->deleteCommunications($user->id);
        $this->removeAvatar($user);
        $user->softDelete();

        // TODO: сделать проверку на возможность полного удаления
        if (!$this->getUndeletedModels($user)) {
            $user->delete();
        }
    }

    public function getUndeletedModels(): bool
    {
        // TODO: сделать проверку на наличие комментариев и т.п.
        return false;
    }

    public function saveCommunication(int $id, array $data): int
    {
        if (CommunicationType::from($data['type']) === CommunicationType::Email) {
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

    public function saveCommunicationManually(int $id, array $data): int
    {
        return $this->repository->saveCommunication($id, $data);
    }

    public function sendCommunicationVerifyEmail(Communication $communication): void
    {
        $this->notificationService->addAndSendRequest(
            channel: NotificationChannel::Email,
            address: $communication->address,
            eventType: SystemEvent::VerifyCommunicationEmail,
            data: $communication->toArray(),
        );
    }

    private function verificationEmail(int $id, array $data): void
    {
        if (!$id) {
            $this->notificationService->addAndSendRequest(
                channel: NotificationChannel::Email,
                address: $data['address'],
                eventType: SystemEvent::VerifyCommunicationEmail,
                data: $data,
            );

            return;
        }

        $current = $this->repository->getCommunicationById($id, (int)$data['user_id']);

        if ($current->address !== $data['address']) {
            $this->notificationService->addAndSendRequest(
                channel: NotificationChannel::Email,
                address: $data['address'],
                eventType: SystemEvent::VerifyCommunicationEmail,
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
    public function getCommunicationsForNotification(int $userId, ?ServiceEvent $eventType): array
    {
        return $this->repository->getCommunicationsForServiceNotificationAvailable($userId, $eventType);
    }

    public function isUserNotificationActive(int $userId, ServiceEvent $event): bool
    {
        return $this->serviceNotificationService->isUserNotificationActive($userId, $event);
    }

    /**
     * @return ServiceNotification[]
     */
    public function getServiceUserNotificationList(int $userId): array
    {
        return $this->serviceNotificationService->getServiceNotificationList($userId);
    }

    public function resetToDefaultUserNotifications(int $userId): void
    {
        $this->serviceNotificationService->resetToDefault($userId);
    }

    public function updateUserServiceNotification(int $userId, ServiceEvent $event, ServiceNotificationDto $dto): void
    {
        $this->serviceNotificationService->updateUserServiceNotification($userId, $event, $dto);
    }

    public function updateNotificationMute(int $userId, ServiceEvent $event, bool $active): void
    {
        $this->serviceNotificationService->updateNotificationMute($userId, $event, $active);
    }

    #endregion
}
