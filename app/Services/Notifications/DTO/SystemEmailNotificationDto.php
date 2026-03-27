<?php

declare(strict_types=1);

namespace App\Services\Notifications\DTO;

use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;

final readonly class SystemEmailNotificationDto
{
    public function __construct(
        public string              $address,
        public SystemEvent         $eventType,
        public NotificationChannel $channel,
        public array               $data = [],
    ) {}
}