<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\NotificationCode;
use App\Notifications\System\VerifyRegistrationCode;
use App\Services\Notifications\DTO\SystemEmailNotificationDto;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final readonly class SystemNotificationService extends AbstractNotificationService
{
    public function verifyRegistrationCode(NotificationCode $notificationCode, int $minutes): void
    {
        $user = $notificationCode->getUser();

        $user->notify(new VerifyRegistrationCode($notificationCode->code, $minutes));
        $user->touch('updated_at');
    }

    public function deleteNotificationCode(int $userId, SystemEvent $event): void
    {
        $this->repository->deleteNotificationCode($userId, $event);
    }

    public function addAndSendRequest(NotificationChannel $channel, string $address, SystemEvent $eventType, mixed $data): void
    {
        $token = md5(uniqid());

        $id = $this->repository->createSubscriptionNotification([
            'address' => $address,
            'channel' => $channel->value,
            'type'    => $eventType->value,
            'token'   => $token,
            'sl'      => json_encode($data),
        ]);

        $notificationToken = $this->repository->getNotificationTokenById($id);

        $this->send(
            new SystemEmailNotificationDto(
                address: $address,
                eventType: $eventType,
                channel: $channel,
                data: [
                    'token' => $notificationToken->token,
                ]
            )
        );
    }

    public function send(SystemEmailNotificationDto $notificationDto): void
    {
        match ($notificationDto->channel) {
            NotificationChannel::Email => $this->sendEmail($notificationDto),
            NotificationChannel::Telegram => throw new \Exception('To be implemented'),
        };
    }

    private function sendEmail(SystemEmailNotificationDto $notificationDto): void
    {
        $view = $this->buildView($notificationDto);

        $mail = new Mailable()->html($view->render())->to($notificationDto->address)
            ->subject($notificationDto->eventType->getLabel())
            ->withSymfonyMessage(function ($message) use ($notificationDto) {
                $message->getHeaders()->addTextHeader('X-Notification-Key', $notificationDto->eventType->value);
            })
            ->from(config('mail.from.address'), config('mail.from.name'));

        try {
            Mail::send($mail);
            Log::info('Email sent to ' . $notificationDto->address . ' for event type: ' . $notificationDto->eventType->getLabel());
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error('Failed to send email to ' . $notificationDto->address . ' for event type: ' . $notificationDto->eventType->getLabel() . '. Error: ' . $error);
        }
    }

    public function buildView(SystemEmailNotificationDto $emailNotificationDto): View
    {
        return match ($emailNotificationDto->eventType) {
            SystemEvent::NewNewsSubscription => View('emails.new_news_subscription')->with([
                'confirmationUrl' => self::getConfirmUrl($emailNotificationDto->data['token']),
                'expireMinutes'   => AbstractNotificationService::EXPIRE_MINUTES,
            ]),
            SystemEvent::VerifyCommunicationEmail => View('emails.verify_communication_email')->with([
                'confirmationUrl' => self::getConfirmUrl($emailNotificationDto->data['token']),
                'expireMinutes'   => AbstractNotificationService::EXPIRE_MINUTES,
            ]),
            SystemEvent::TravelInvite => View('emails.travel_invite')->with(['dto' => $emailNotificationDto->data['dto']]),
            default => throw new \Exception('Unsupported notification type: ' . $emailNotificationDto->eventType->getLabel()),
        };
    }
}