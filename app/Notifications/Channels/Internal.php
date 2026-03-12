<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class Internal
{
    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toInternalDatabase')) {
            return;
        }

        $data = $notification->toInternalDatabase($notifiable);

        DB::table('user_notifications')->insert([
            'user_id'    => $notifiable->getKey(),
            'message'    => $data['message'],
            'created_at' => now(),
        ]);
    }
}