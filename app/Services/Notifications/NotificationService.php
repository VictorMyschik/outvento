<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\MessageLog\EmailLog;
use App\Models\Notification\UserNotificationSetting;
use App\Notifications\NewsNotification;
use App\Repositories\System\SettingsRepositoryInterface;
use App\Services\Notifications\Enum\NotificationType;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

final readonly class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $repository,
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SettingsRepositoryInterface     $settingsRepository,
    ) {}

    public static function getUnsubscribeUrl(string $token): string
    {
        return env('FRONT_HOST') . '/unsubscribe?token=' . $token;
    }

    public function saveUserSetting(int $id, array $data): int
    {
        $data['token'] = md5(uniqid());

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

    public function getSubscribersList(NotificationType $type): array
    {
        return array_merge(
            $this->repository->getSubscriptionUsersList($type),
            $this->subscriptionRepository->getListByType($type),
        );
    }

    public function isNotificationEnabled(): bool
    {
        return $this->settingsRepository->notificationEnabled();
    }

    public function sendNewsNotification(NotificationRecipientInterface $recipient, array $newsList): void
    {
        $unsubscribeUrl = self::getUnsubscribeUrl(
            $recipient->getUnsubscribeToken(NotificationType::News)
        );

        $recipient->notify(new NewsNotification($newsList, $unsubscribeUrl));
    }

    public function customEmailNotify(string $to, Mailable $email, NotificationType $type): void
    {
        try {
            Mail::to($to)->send($email->from(config('mail.from.address'), config('mail.from.name')));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $log = new EmailLog();
        $log->setType($type);
        $log->setEmail($to);
        $log->setSubject($email->subject);
        $log->setEmailBody($email->render());
        $log->setStatus((bool)($result ?? null));
        $log->setError($error ?? null);
        $log->save();
    }
}
