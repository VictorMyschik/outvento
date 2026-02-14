<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\NotificationToken;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Notifications\NewsNotification;
use App\Repositories\System\SettingsRepositoryInterface;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Enum\SystemEvent;
use App\Services\Notifications\Resolvers\CommunicationChannelSupportResolver;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

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

    public function sendNewsNotification(NotificationRecipientInterface $recipient, array $newsList): void
    {
        $unsubscribeUrl = self::getUnsubscribeUrl($recipient->getUnsubscribeToken());

        $recipient->notify(new NewsNotification($newsList, $unsubscribeUrl));
    }

    /**
     * Пока не используется
     */
    public function customEmailNotify(string $to, Mailable $email, string $type): void
    {
        try {
            Mail::to($to)->send($email->from(config('mail.from.address'), config('mail.from.name')));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error('Failed to send custom email to ' . $to . ' with type ' . $type . '. Error: ' . $error);

            return;
        }

        Log::info('Custom email sent to ' . $to . ' with type ' . $type);
    }

    public function confirmNotificationToken(string $token, array $info): void
    {
        // TODO: обработка подтверждения токена, активация подписки и т.д.
    }
}
