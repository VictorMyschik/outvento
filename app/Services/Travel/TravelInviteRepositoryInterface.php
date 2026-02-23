<?php

declare(strict_types=1);

namespace App\Services\Travel;

use App\Services\Notifications\Enum\ServiceEvent;

interface TravelInviteRepositoryInterface
{
    public function saveInvite(int $travelId, string $email, ?int $userId = null): int;

    public function getListByTravel(int $travelId): array;

    public function removeTravelInvite(int $inviteId): void;

    public function isMute(int $userId, ServiceEvent $event): bool;

    public function getListByUser(int $userId): array;
}