<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Services\Notifications\InternalNotificationService;
use Illuminate\Notifications\Notification;

final readonly class Internal
{
    public function __construct(
        private InternalNotificationService $service,
    ) {}

    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toInternalDatabase')) {
            return;
        }

        $data = $notification->toInternalDatabase($notifiable);
        $this->service->saveUserNotification(0, $data);
    }
}