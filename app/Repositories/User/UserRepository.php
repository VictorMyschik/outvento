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
    public function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateUser(int $id, array $data): void
    {
        $this->db->table('users')->where('id', $id)->update($data);
    }

    public function saveCommunicate(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Communication::getTableName())->where('id', $id)->where('user_id', $data['user_id'])->update($data);

            return $id;
        }

        return $this->db->table(Communication::getTableName())->insertGetId($data);
    }

    public function getCommunicates(int $userId, Language $language): array
    {
        return $this->db->table(Communication::getTableName())
            ->join(CommunicationType::getTableName(), CommunicationType::getTableName() . '.id', '=', Communication::getTableName() . '.type_id')
            ->where('user_id', $userId)
            ->selectRaw(
                implode(',', [
                    Communication::getTableName() . '.*',
                    CommunicationType::getTableName() . '.name_' . $language->getCode() . ' AS communication_type'
                ])
            )
            ->get()->all();
    }

    public function deleteCommunicates(int $userId): void
    {
        $this->db->table(Communication::getTableName())->where('user_id', $userId)->delete();
    }

    public function deleteCommunicate(int $userId, int $id): void
    {
        $this->db->table(Communication::getTableName())->where('user_id', $userId)->where('id', $id)->delete();
    }
}
