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
    public function customEmailNotify(string $to, Mailable $email, ServiceEvent $type): void
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

    public function addAndSendRequest(NotificationChannel $channel, string $address, SystemEvent $eventType, mixed $data): void
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
            SystemEvent::NewNewsSubscription => View('emails.new_news_subscription')->with([
                'confirmationUrl' => self::getConfirmUrl($notificationToken->token),
                'expireMinutes'   => AbstractNotificationService::EXPIRE_MINUTES,
            ]),
            SystemEvent::VerifyCommunicationEmail => View('emails.verify_communication_email')->with([
                'confirmationUrl' => self::getConfirmUrl($notificationToken->token),
                'expireMinutes'   => AbstractNotificationService::EXPIRE_MINUTES,
            ]),
            default => throw new \Exception('Unsupported notification type: ' . $notificationToken->getType()->getLabel()),
        };
    }

    public function confirmNotificationToken(string $token, array $info): void
    {
        // TODO: обработка подтверждения токена, активация подписки и т.д.
    }
}
