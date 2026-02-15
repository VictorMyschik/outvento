<?php

declare(strict_types=1);

namespace App\Repositories\Subscription;

use App\Models\Promo\Subscription;
use App\Models\Promo\SubscriptionLegalAcceptance;
use App\Repositories\DatabaseRepository;
use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Promo\Enum\Status;
use App\Services\Promo\SubscriptionRepositoryInterface;

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
            $this->db->table(Subscription::getTableName())->where('id', $id)->update($data);

            return $id;
        }

        return $this->db->table(Subscription::getTableName())->insertGetId($data);
    }

    public function deleteSubscription(string $token): void
    {
        $this->db->table(Subscription::getTableName())->where('token', $token)->delete();
    }

    public function getSubscriptionByEmailAndEvent(string $email, PromoEvent $promoEvent): ?Subscription
    {
        return Subscription::where('email', $email)
            ->where('event', $promoEvent->value)
            ->first();
    }

    public function createSubscriptionLegalInfo(int $subscriptionId, array $legalDocuments): void
    {
        $insert = [];

        foreach ($legalDocuments as $documentId) {
            $insert[] = [
                'subscription_id'   => $subscriptionId,
                'legal_document_id' => $documentId,
                'accepted_at'       => now(),
            ];
        }

        $this->db->table(SubscriptionLegalAcceptance::getTableName())->insert($insert);
    }

    public function confirmSubscriptionLegalInfo(int $subscriptionId): void
    {
        $this->db->table(SubscriptionLegalAcceptance::getTableName())
            ->where('subscription_id', $subscriptionId)
            ->update(['accepted_at' => now()]);
    }

    public function getSubscribersByEvent(PromoEvent $promoEvent): array
    {
        return Subscription::where('event', $promoEvent->value)
            ->where('status', Status::Confirmed->value)
            ->get()
            ->all();
    }
}
