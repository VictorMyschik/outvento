<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use App\Repositories\DatabaseRepository;
use App\Services\System\Enum\Language;

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
        return $this->db->table('role')
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
            $this->db->table(Communication::getTableName())->where('id', $id)->where('user_id', $data['user_id'])->update($data);

            return $id;
        }

        return $this->db->table(Communication::getTableName())->insertGetId($data);
    }

    public function getCommunications(int $userId, Language $language): array
    {
        return $this->db->table(Communication::getTableName())
            ->join(CommunicationType::getTableName(), CommunicationType::getTableName() . '.id', '=', Communication::getTableName() . '.type_id')
            ->where('user_id', $userId)
            ->selectRaw(
                implode(',', [
                    Communication::getTableName() . '.*',
                    CommunicationType::getTableName() . '.name_' . $language->getCode() . ' AS communication_type',
                    CommunicationType::getTableName() . '.code AS code'
                ])
            )
            ->get()->all();
    }

    public function getCommunicationById(int $id, int $userId): ?Communication
    {
        return Communication::where('id', $id)->where('user_id', $userId)->first();
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

    public function getCommunicationsByTypeCodes(int $userId, array $types): array
    {
        $typeTableName = CommunicationType::getTableName();
        $communicationTableName = Communication::getTableName();

        return $this->db->table($communicationTableName)
            ->join($typeTableName, $typeTableName . '.id', '=', $communicationTableName . '.type_id')
            ->where('user_id', $userId)
            ->whereIn($typeTableName . '.code', $types)
            ->selectRaw(implode(',', [
                $communicationTableName . '.id as id',
                $communicationTableName . '.address as address',
                $typeTableName . '.code AS code'
            ]))
            ->get()->all();
    }

    public function getCommunicationChannelTypes(array $channels): array
    {
        return CommunicationType::whereIn('code', $channels)->get()->all();
    }
}
