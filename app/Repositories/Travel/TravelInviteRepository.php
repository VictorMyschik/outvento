<?php

declare(strict_types=1);

namespace App\Repositories\Travel;

use App\Models\Notification\NotificationMute;
use App\Models\Travel\Travel;
use App\Models\TravelInvite;
use App\Repositories\DatabaseRepository;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Travel\TravelInviteRepositoryInterface;

final readonly class TravelInviteRepository extends DatabaseRepository implements TravelInviteRepositoryInterface
{
    public function saveInvite(int $travelId, string $email, ?int $userId = null): int
    {
        $table = TravelInvite::getTableName();

        $this->db->table($table)->updateOrInsert([
            'travel_id' => $travelId,
            'email'     => $email,
        ], [
            'user_id' => $userId,
        ]);

        return $this->db->table($table)->where([
            'travel_id' => $travelId,
            'email'     => $email,
        ])->value('id');
    }

    public function getListByTravel(int $travelId): array
    {
        return TravelInvite::where('travel_id', $travelId)->get()->all();
    }

    public function removeTravelInvite(int $inviteId): void
    {
        $this->db->table(TravelInvite::getTableName())->where('id', $inviteId)->delete();
    }

    public function isMute(int $userId, ServiceEvent $event): bool
    {
        return $this->db->table(NotificationMute::getTableName())
            ->where('user_id', $userId)
            ->where('event', $event->value)
            ->exists();
    }

    public function getListByUser(int $userId): array
    {
        return TravelInvite::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()->all();
    }
}