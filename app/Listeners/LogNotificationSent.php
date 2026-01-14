<?php

namespace App\Listeners;

use App\Models\MessageLog\EmailLog;
use Illuminate\Notifications\Events\NotificationSent;

class LogNotificationSent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
       /* NotificationLog::create([
            'notifiable_type' => get_class($event->notifiable),
            'notifiable_id' => $event->notifiable->id ?? null,
            'notification_key' => $event->notification::KEY ?? null,
            'channel' => $event->channel,
            'status' => 'failed',
            'error' => $event->exception?->getMessage(),
        ]);


        $log = new EmailLog();
        $log->setType($type);
        $log->setEmail($to);
        $log->setSubject($email->subject);
        $log->setEmailBody($email->render());
        $log->setStatus((bool)($result ?? null));
        $log->setError($event->exception?->getMessage());
        $log->save();*/
    }
}
