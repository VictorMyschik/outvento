<?php

declare(strict_types=1);

namespace App\Services\Promo;

use App\Models\Promo\Subscription;
use App\Services\Notifications\Enum\PromoEvent;

interface SubscriptionRepositoryInterface
{
    public function getSubscriptionById(int $id): ?Subscription;

    public function getSubscriptionByToken(string $token): ?Subscription;

    public function saveSubscription(int $id, array $data): int;

    public function deleteSubscription(string $token): void;

    public function getSubscriptionByEmailAndEvent(string $email, PromoEvent $promoEvent): ?Subscription;

    public function createSubscriptionLegalInfo(int $subscriptionId, array $legalDocuments): void;

    public function confirmSubscriptionLegalInfo(int $subscriptionId): void;

    public function getSubscribersByEvent(PromoEvent $promoEvent): array;
}