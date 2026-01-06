<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogNotificationFailed
{
    public function handle(object $event): void
    {
        NotificationLog::create([
            'notifiable_type' => get_class($event->notifiable),
            'notifiable_id' => $event->notifiable->id ?? null,
            'notification_key' => $event->notification::KEY,
            'channel' => $event->channel,
            'entity_type' => get_class($event->notification->news ?? null),
            'entity_id' => $event->notification->news->id ?? null,
            'status' => 'sent',
        ]);
    }
}
