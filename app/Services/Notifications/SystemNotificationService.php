<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\NotificationToken;
use App\Services\Notifications\Enum\SystemEvent;
use App\Services\Notifications\Enum\NotificationChannel;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final readonly class SystemNotificationService extends AbstractNotificationService
{
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