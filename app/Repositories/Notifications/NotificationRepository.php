<?php

declare(strict_types=1);

namespace App\Repositories\Notifications;

use App\Models\Email\EmailLog;
use App\Models\Notification\NotificationMute;
use App\Models\Notification\ServiceNotification;
use App\Models\NotificationCode;
use App\Models\NotificationToken;
use App\Models\User;
use App\Models\UserNotification;
use App\Repositories\DatabaseRepository;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Enum\SystemEvent;
use App\Services\Notifications\NotificationRepositoryInterface;

final readonly class NotificationRepository extends DatabaseRepository implements NotificationRepositoryInterface
{
    public function deleteUserSetting(int $id): void
    {
        $this->db->table(ServiceNotification::getTableName())->where('id', $id)->delete();
    }

    public function saveServiceUserNotification(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(ServiceNotification::getTableName())->where('id', $id)->update($data);
            return $id;
        }

        return $this->db->table(ServiceNotification::getTableName())->insertGetId($data);
    }

    /**
     * @return User[]
     */
    public function getSubscriptionUsersList(ServiceEvent $event): array
    {
        return User::join(ServiceNotification::getTableName(), 'users.id', '=', ServiceNotification::getTableName() . '.user_id')
            ->where(ServiceNotification::getTableName() . '.event', $event->value)
            ->groupBy(ServiceNotification::getTableName() . '.user_id', 'users.id')
            ->get(User::getTableName() . '.*')->all();
    }

    public function createSubscriptionNotification(array $dto): int
    {
        $this->db->table(NotificationToken::getTableName())->where([
            'address' => $dto['address'],
            'type'    => $dto['type'],
        ])->delete();

        return $this->db->table(NotificationToken::getTableName())->insertGetId($dto);
    }

    public function getNotificationTokenById(int $id): NotificationToken
    {
        return NotificationToken::loadByOrDie($id);
    }

    public function purgeServiceUserNotifications(int $userId): void
    {
        $this->db->table(ServiceNotification::getTableName())->where('user_id', $userId)->delete();
    }

    public function getServiceUserNotificationList(int $userId, ?int $eventTypeId = null): array
    {
        return ServiceNotification::where('user_id', $userId)
            ->when($eventTypeId !== null, function ($q) use ($userId, $eventTypeId) {
                $q->where('event', $eventTypeId);
            })->get()->all();
    }

    public function isUserNotificationActive(int $userId, ServiceEvent $event): bool
    {
        return !$this->db->table(NotificationMute::getTableName())
            ->where('user_id', $userId)
            ->where('event', $event->value)
            ->exists();
    }

    public function muteUserNotification(int $userId, ServiceEvent $event): void
    {
        $this->db->table(NotificationMute::getTableName())->insertOrIgnore([
            'user_id' => $userId,
            'event'   => $event->value,
        ]);
    }

    public function unmuteUserNotification(int $userId, ServiceEvent $event): void
    {
        $this->db->table(NotificationMute::getTableName())
            ->where('user_id', $userId)
            ->where('event', $event->value)
            ->delete();
    }

    public function deleteServiceNotificationsByEventAndChannel(int $userId, ServiceEvent $event, NotificationChannel $channel): void
    {
        $this->db->table(ServiceNotification::getTableName())
            ->where('user_id', $userId)
            ->where('event', $event->value)
            ->where('channel', $channel->value)
            ->delete();
    }

    public function deleteServiceNotifications(int $id): void
    {
        $this->db->table(ServiceNotification::getTableName())->where('id', $id)->delete();
    }

    public function setEmailLog(array $data): void
    {
        $this->db->table(EmailLog::getTableName())->insert($data);
    }

    public function deleteNotificationCode(int $userId, SystemEvent $event): void
    {
        $this->db->table(NotificationCode::getTableName())->where([
            'user_id' => $userId,
            'type'    => $event->value,
        ])->delete();
    }

    public function getInternalNotificationsByUserId(int $userId): array
    {
        return UserNotification::where('user_id', $userId)->orderBy('id', 'desc')->get()->all();
    }

    public function addInternalUserNotification(array $data): void
    {
        $this->db->table(UserNotification::getTableName())->insert($data);
    }

    public function deleteInternalNotificationById(int $userId, int $notificationId): void
    {
        $this->db->table(UserNotification::getTableName())->where(['user_id' => $userId, 'id' => $notificationId])->delete();
    }

    public function updateInternalNotification(int $id, array $data): void
    {
        $this->db->table(UserNotification::getTableName())->where('id', $id)->update($data);
    }

    public function purgeInternalUserNotifications(int $userId): void
    {
        $this->db->table(UserNotification::getTableName())->where('user_id', $userId)->delete();
    }

    public function markAllInternalNotificationsAsRead(int $userId): void
    {
        $this->db->table(UserNotification::getTableName())->where('user_id', $userId)->update(['read_at' => now()]);
    }

    public function saveUserNotification(int $id, array $data): void
    {
        if ($id > 0) {
            $this->db->table(UserNotification::getTableName())->where('id', $id)->update($data);

            return;
        }

        $this->db->table(UserNotification::getTableName())->insert($data);
    }
}
