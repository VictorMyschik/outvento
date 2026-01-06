<?php

declare(strict_types=1);

namespace App\Services\Subscription;

use App\Models\Subscription\Subscription;
use App\Services\Notifications\Enum\NotificationType;
use App\Services\System\Enum\Language;

interface SubscriptionRepositoryInterface
{
    public function getSubscriptionById(int $id): ?Subscription;

    public function getSubscriptionByToken(string $token): ?Subscription;

    public function getSubscriptionByEmail(string $email): array;

    public function saveSubscription(int $id, array $data): int;

    public function deleteSubscription(string $token): void;

    public function deleteSubscriptionByEmail(string $email): void;

    public function getListByType(NotificationType $type): array;
}
