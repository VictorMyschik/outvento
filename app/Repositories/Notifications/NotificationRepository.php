<?php

declare(strict_types=1);

namespace App\Repositories\Notifications;

use App\Models\Notification\UserNotificationSetting;
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
}
