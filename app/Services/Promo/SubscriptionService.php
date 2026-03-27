<?php

declare(strict_types=1);

namespace App\Services\Promo;

use App\Models\Promo\Subscription;
use App\Models\User;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\Notifications\SystemNotificationService;
use App\Services\Other\LegalDocuments\LegalDocumentsService;
use App\Services\Promo\DTO\SubscriptionDto;
use App\Services\Promo\Enum\Status;
use App\Services\Promo\Resolvers\SubscriptionNotifyResolver;

final readonly class SubscriptionService
{
    public function __construct(
        private SubscriptionRepositoryInterface $repository,
        private LegalDocumentsService           $legalDocumentsService,
        private SystemNotificationService       $notificationService
    ) {}

    public function getSubscriptionById(int $id): ?Subscription
    {
        return $this->repository->getSubscriptionById($id);
    }

    public function getSubscriptionByToken(string $token): ?Subscription
    {
        return $this->repository->getSubscriptionByToken($token);
    }

    public function getUserSubscriptionByEvent(User $user, PromoEvent $event): ?Subscription
    {
        return $this->repository->getSubscriptionByEmailAndEvent($user->email, $event);
    }

    public function createSubscriptionWithNotify(SubscriptionDto $dto): void
    {
        $exists = $this->repository->getSubscriptionByEmailAndEvent($dto->email, $dto->event);

        if ($exists) {
            if ($exists->getStatus() === Status::Pending) {
                $this->repository->deleteSubscription($exists->token);
            } elseif ($exists->getStatus() !== Status::Revoked) {
                return;
            }
        }

        $token = md5(uniqid());

        $id = $this->repository->saveSubscription(0, [
            'email'        => $dto->email,
            'language'     => $dto->language->value,
            'event'        => $dto->event->value,
            'status'       => Status::Pending->value,
            'token'        => $token,
            'confirmed_at' => null,
            'revoked_at'   => null,
            'optin_at'     => now(),
            'optin_source' => $dto->source->value,
        ]);

        $legalDocuments = $this->legalDocumentsService->getLegalDocumentsByLanguage($dto->language);
        $this->repository->createSubscriptionLegalInfo($id, $legalDocuments);

        $this->notificationService->addAndSendRequest(
            channel: NotificationChannel::Email,
            address: $dto->email,
            eventType: SubscriptionNotifyResolver::getSystemEventByPromoEvent($dto->event),
            data: ['token' => $token, 'event' => $dto->event->value],
        );
    }

    public function confirmSubscription(Subscription $subscription): void
    {
        $this->repository->confirmSubscriptionLegalInfo($subscription->id());
        $this->repository->saveSubscription($subscription->id(), [
            'status'       => Status::Confirmed->value,
            'confirmed_at' => now(),
        ]);
    }

    public function deleteSubscription(string $token): void
    {
        $this->repository->deleteSubscription($token);
    }

    public function revokeSubscription(string $token): void
    {
        $this->repository->revokeSubscription($token);
    }

    /**
     * @return NotificationRecipientInterface[]
     */
    public function getSubscribersList(PromoEvent $event): array
    {
        return $this->repository->getSubscribersByEvent($event);
    }
}
