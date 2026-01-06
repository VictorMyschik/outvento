<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\DatabaseRepository;

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
}