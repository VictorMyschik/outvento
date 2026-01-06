<?php

namespace App\Services\Notifications;

interface NotificationRecipientInterface
{
    public function notify(mixed $instance);
}