<?php

declare(strict_types=1);

namespace App\Repositories\Subscription;

use App\Models\Subscription\Subscription;
use App\Repositories\DatabaseRepository;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\SubscriptionRepositoryInterface;

final readonly class SubscriptionRepository extends DatabaseRepository implements SubscriptionRepositoryInterface
{
    public function getSubscriptionById(int $id): ?Subscription
    {
        return Subscription::loadBy($id);
    }

    public function getSubscriptionByToken(string $token): ?Subscription
    {
        return Subscription::where('token', $token)->first();
    }

    public function saveSubscription(int $id, array $data): int
    {
        if ($id > 0) {
            $this->db->table(Subscription::getTableName())->update($data);

            return $id;
        }

        return $this->db->table(Subscription::getTableName())->insertGetId($data);
    }

    public function deleteSubscription(string $token): void
    {
        $this->db->table(Subscription::getTableName())->where('token', $token)->delete();
    }

    public function getListByType(ServiceEvent $type): array
    {
        return Subscription::where('type', $type->value)->get()->all();
    }

    public function getSubscriptionByEmail(string $email): array
    {
        return Subscription::where('email', $email)->get()->all();
    }

    public function deleteSubscriptionByEmail(string $email): void
    {
        $this->db->table(Subscription::getTableName())->where('email', $email)->delete();
    }

    public function deleteSubscriptionByEmailAndType(ServiceEvent $type, string $email): void
    {
        $this->db->table(Subscription::getTableName())->where('email', $email)->where('type', $type->value)->delete();
    }
}
