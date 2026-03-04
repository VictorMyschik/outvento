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
use App\Services\System\Enum\Language;
use App\Services\Travel\TravelService;
use App\Services\User\Enum\CommunicationType;
use App\Services\User\Enum\VerificationStatus;
use Illuminate\Http\UploadedFile;
use Nette\Utils\Random;

final readonly class UserService
{
    public function __construct(
        private UserUploadService          $uploadService,
        private UserRepository             $repository,
        private AuthService                $authService,
        private SystemNotificationService  $notificationService,
        private ServiceNotificationService $serviceNotificationService,
        private TravelService              $travelService,
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

        if (!empty($data['email_verified_at'])) {
            $this->notificationService->deleteNotificationCode($user->id, SystemEvent::RegistrationConfirmation);
        }

        return $this->repository->getUserById($user->id);
    }

    public function findUser(string $address): ?User
    {
        if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
            $address = $this->repository->getEmailByName($address);

            if (!$address) {
                return null;
            }
        }

        return $this->repository->getUserByEmail($address);
    }

    public function getCommunicationByToken(string $token): ?Communication
    {
        return $this->repository->getCommunicationByToken($token);
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

        if (CommunicationType::from($data['type']) === CommunicationType::Telegram) {
            if (!$id) {
                $data['address_ext'] = md5(Random::generate());
            }
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
    public function getCommunicationsForNotification(int $userId): array
    {
        return $this->repository->getCommunicationsForServiceNotificationAvailable($userId);
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

    public function getUserLanguages(User $user, Language $language): array
    {
        return $this->repository->getUserLanguages($user, $language);
    }

    public function updateUserLanguages(User $user, array $languages): void
    {
        $this->repository->updateUserLanguages($user, $languages);
    }

    public function deleteUserLanguages(User $user): void
    {
        $this->repository->deleteUserLanguages($user);
    }

    public function getStorageUsed(int $userId): string
    {
        $personalStorage = $this->uploadService->getUserStorageUsed($userId);
        $travelStorage = $this->travelService->getFullUserMediaSize($userId);

        $bytes = $personalStorage + $travelStorage;

        return $this->formatBytes($bytes);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);

        return sprintf('%.2f', $bytes / pow(1024, $factor)) . ' ' . $units[(int)$factor];
    }
}
