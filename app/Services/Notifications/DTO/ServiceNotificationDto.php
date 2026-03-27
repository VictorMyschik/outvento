<?php

declare(strict_types=1);

namespace App\Services\Notifications\DTO;

use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;

final readonly class ServiceNotificationDto
{
    public function __construct(
        public int $userId,
        public ServiceEvent $event,
        public NotificationChannel $channel,
        public ?int $communicationId = null,
    ) {}
}