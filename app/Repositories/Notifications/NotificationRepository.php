<?php

declare(strict_types=1);

namespace App\Repositories\Notifications;

use App\Models\Notification\NotificationEventType;
use App\Models\Notification\UserNotificationSetting;
use App\Models\NotificationToken;
use App\Models\User;
use App\Repositories\DatabaseRepository;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\NotificationRepositoryInterface;

final readonly class NotificationRepository extends DatabaseRepository implements NotificationRepositoryInterface
{
    public function deleteUserSetting(int $id): void
    {
        $this->db->table(UserNotificationSetting::getTableName())->where('id', $id)->delete();
    }

    public function getUserNotificationSettingById(int $id): ?UserNotificationSetting
    {
        return UserNotificationSetting::loadBy($id);
    }

    public function saveUserSetting(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(UserNotificationSetting::getTableName())->where('id', $id)->update($data);
            return $id;
        }

        return $this->db->table(UserNotificationSetting::getTableName())->insertGetId($data);
    }

    /**
     * @return User[]
     */
    public function getSubscriptionUsersList(EventType $type): array
    {
        return User::join(UserNotificationSetting::getTableName(), 'users.id', '=', UserNotificationSetting::getTableName() . '.user_id')
            ->where(UserNotificationSetting::getTableName() . '.event_type', $type->value)
            ->where(UserNotificationSetting::getTableName() . '.active', true)
            ->groupBy(UserNotificationSetting::getTableName() . '.user_id', 'users.id')
            ->get(User::getTableName() . '.*')->all();
    }

    public function createNewsSubscriptionNotification(array $dto): int
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

    public function deleteAllUserSettings(int $userId): void
    {
        $this->db->table(UserNotificationSetting::getTableName())->where('user_id', $userId)->delete();
    }

    public function insertUserSettings(array $data): void
    {
        $this->db->table(UserNotificationSetting::getTableName())->insert($data);
    }

    public function getUserNotificationSettingsList(int $userId, ?int $eventTypeId = null): array
    {
        return UserNotificationSetting::where('user_id', $userId)
            ->when($eventTypeId !== null, function ($q) use ($userId, $eventTypeId) {
                $q->where('event_type_id', $eventTypeId);
            })->get()->all();
    }

    public function getNotificationTypesForUser(User $user): array
    {
        return NotificationEventType::join('model_roles', 'model_roles.model_id', '=', NotificationEventType::getTableName() . '.id')
            ->where('model_roles.table_name', NotificationEventType::class)
            ->whereIn('model_roles.role_id', $user->getRoles()->pluck('id')->toArray())
            ->groupBy(NotificationEventType::getTableName() . '.id')
            ->get(NotificationEventType::getTableName() . '.*')->all();
    }
}
