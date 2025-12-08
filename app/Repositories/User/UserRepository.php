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
        if (!empty($data)) {
            $this->db->table('users')->where('id', $id)->update($data);
        }
    }

    public function getUserFullName(array $ids): array
    {
        return $this->db->table('users')
            ->whereIn('id', $ids)
            ->select(['id', 'first_name', 'last_name'])
            ->get()
            ->mapWithKeys(fn($user) => [$user->id => trim("{$user->first_name} {$user->last_name}")])
            ->toArray();
    }
}