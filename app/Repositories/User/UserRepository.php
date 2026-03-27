<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\LanguageName;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Models\UserLanguage;
use App\Repositories\DatabaseRepository;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\VerificationStatus;

final readonly class UserRepository extends DatabaseRepository
{
    public function createUser(array $data): int
    {
        return $this->db->table(User::getTableName())->insertGetId($data);
    }

    public function deleteAvatar(int $userId): void
    {
        $this->db->table(User::getTableName())->where('id', $userId)->update(['avatar' => null]);
    }

    public function updateUserRoles(int $userId, array $roleIds): void
    {
        $this->db->table('role_users')->where('user_id', $userId)->delete();

        if (count($roleIds)) {
            $data = array_map(static function ($roleId) use ($userId) {
                return [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ];
            }, $roleIds);

            $this->db->table('role_users')->insert($data);
        }
    }

    /**
     * @param array $roles UserRole[]
     */
    public function getIdsForRoles(array $roles): array
    {
        return $this->db->table('roles')
            ->whereIn('slug', array_map(static fn($role) => $role->value, $roles))
            ->pluck('id')->all();
    }

    public function getEmailByName(string $name): ?string
    {
        return $this->db->table(User::getTableName())->where('name', $name)->value('email');
    }

    public function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateUser(int $id, array $data): void
    {
        $this->db->table(User::getTableName())->where('id', $id)->update($data);
    }

    public function saveCommunication(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Communication::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(Communication::getTableName())->insertGetId($data);
    }

    public function getCommunications(int $userId): array
    {
        return Communication::where('user_id', $userId)->get()->all();
    }

    public function getCommunicationById(int $id, int $userId): Communication
    {
        return Communication::where('id', $id)->where('user_id', $userId)->firstOrFail();
    }

    public function deleteCommunications(int $userId): void
    {
        $this->db->table(Communication::getTableName())->where('user_id', $userId)->delete();
    }

    public function deleteAllCommunications(int $userId): void
    {
        $this->db->table(Communication::getTableName())->where('user_id', $userId)->delete();
    }

    public function deleteCommunication(int $userId, int $id): void
    {
        $this->db->table(Communication::getTableName())->where('user_id', $userId)->where('id', $id)->delete();
    }

    public function hasCommunicationByAddress(string $address): bool
    {
        return $this->db->table(Communication::getTableName())->where('address', $address)->exists();
    }

    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Вернёт список коммуникаций пользователя, которые доступны для настройки уведомлений о событии $eventType
     */
    public function getCommunicationsForServiceNotificationAvailable(int $userId): array
    {
        return Communication::where(Communication::getTableName() . '.user_id', $userId)
            ->where('verification_status', VerificationStatus::Verified->value)
            ->get()->all();
    }

    public function getCommunicationByToken(string $token): ?Communication
    {
        return Communication::where('address_ext', $token)->first();
    }

    public function getUserLanguages(User $user, Language $language): array
    {
        return $this->db->table(LanguageName::getTableName())
            ->join(UserLanguage::getTableName(), LanguageName::getTableName() . '.language_id', '=', UserLanguage::getTableName() . '.language_id')
            ->where(UserLanguage::getTableName() . '.user_id', $user->id)
            ->where(LanguageName::getTableName() . '.locale', $language->getCode())
            ->pluck(LanguageName::getTableName() . '.name', UserLanguage::getTableName() . '.language_id')
            ->all();
    }

    public function updateUserLanguages(User $user, array $languages): void
    {
        $this->db->table(UserLanguage::getTableName())->where('user_id', $user->id)->delete();

        if (count($languages)) {
            $data = array_map(static function ($languageId) use ($user) {
                return [
                    'user_id'     => (int)$user->id,
                    'language_id' => (int)$languageId,
                ];
            }, $languages);

            $this->db->table(UserLanguage::getTableName())->insert($data);
        }
    }

    public function deleteUserLanguages(User $user): void
    {
        $this->db->table(UserLanguage::getTableName())->where('user_id', $user->id)->delete();
    }
}
