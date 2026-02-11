<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Notification\UserNotificationSetting;
use App\Models\NotificationToken;
use App\Models\User;
use App\Notifications\NewsNotification;
use App\Repositories\System\SettingsRepositoryInterface;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\System\Enum\Language;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final readonly class NotificationService
{
    public const int EXPIRE_MINUTES = 20;

    public function __construct(
        private NotificationRepositoryInterface $repository,
        private SettingsRepositoryInterface     $settingsRepository,
    ) {}

    public function resetToDefault(int $userId): void
    {
        $this->repository->deleteAllUserSettings($userId);
    }

    public function updateFullUserNotificationSetting(int $userId, array $data): void
    {
        $this->repository->deleteAllUserSettings($userId);
        $this->repository->insertUserSettings($data);
    }

    public function getUserNotificationSettingsList(int $userId, ?int $eventTypeId = null): array
    {
        return $this->repository->getUserNotificationSettingsList($userId, $eventTypeId);
    }

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

    public function getNotificationTypesForUser(User $user): array
    {
        return $this->repository->getNotificationTypesForUser($user);
    }

    public function getAuthSubscribersList(EventType $type): array
    {
        return $this->repository->getSubscriptionUsersList($type);
    }

    public function getSubscribersList(EventType $type): array
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
    public function customEmailNotify(string $to, Mailable $email, EventType $type): void
    {
        try {
            Mail::to($to)->send($email->from(config('mail.from.address'), config('mail.from.name')));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error('Failed to send custom email to ' . $to . ' with type ' . $type->getLabel() . '. Error: ' . $error);

            return;
        }

        Log::info('Custom email sent to ' . $to . ' with type ' . $type->getLabel());
    }

    public function addAndSendRequest(NotificationChannel $channel, string $address, EventType $eventType, mixed $data): void
    {
        $token = md5(uniqid());

        $id = $this->repository->createNewsSubscriptionNotification([
            'address' => $address,
            'channel' => $channel->value,
            'type'    => $eventType->value,
            'token'   => $token,
            'sl'      => json_encode($data),
        ]);

        $notificationToken = $this->repository->getNotificationTokenById($id);

        $this->send($notificationToken);
    }

    public function send(NotificationToken $notificationToken): void
    {
        match ($notificationToken->getChannel()) {
            NotificationChannel::Email => $this->sendEmailConfirmation($notificationToken),
            NotificationChannel::Telegram => throw new \Exception('To be implemented'),
        };
    }

    private function sendEmailConfirmation(NotificationToken $notificationToken): void
    {
        if ($notificationToken->getChannel() === NotificationChannel::Email) {
            $view = $this->buildView($notificationToken);

            $mail = new Mailable()->html($view->render())->to($notificationToken->address)
                ->subject($notificationToken->getType()->getLabel())
                ->from(config('mail.from.address'), config('mail.from.name'));

            try {
                Mail::send($mail);
                Log::info('Email sent to ' . $notificationToken->address . ' for event type: ' . $notificationToken->getType()->getLabel());
            } catch (\Exception $e) {
                $error = $e->getMessage();
                Log::error('Failed to send email to ' . $notificationToken->address . ' for event type: ' . $notificationToken->getType()->getLabel() . '. Error: ' . $error);
            }

            return;
        }

        throw new \Exception('Unsupported channel confirmation: ' . $notificationToken->getChannel()->getLabel());
    }

    public function buildView(NotificationToken $notificationToken): View
    {
        return match ($notificationToken->getType()) {
            EventType::NewNewsSubscription => View('emails.new_news_subscription')->with([
                'confirmationUrl' => self::getConfirmUrl($notificationToken->token),
                'expireMinutes'   => NotificationService::EXPIRE_MINUTES,
            ]),
        };
    }

    public function confirmNotificationToken(string $token, array $info): void {}
}
