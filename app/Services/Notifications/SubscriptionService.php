<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Subscription\Subscription;
use App\Notifications\NewNewsSubscriptionNotification;
use App\Services\Notifications\DTO\SubscriptionDto;
use App\Services\Notifications\Enum\NotificationType;
use App\Services\System\Enum\Language;

final readonly class SubscriptionService
{
    public function __construct(
        private SubscriptionRepositoryInterface $repository
    ) {}

    public function getSubscriptionById(int $id): ?Subscription
    {
        return $this->repository->getSubscriptionById($id);
    }

    public function getSubscriptionByToken(string $token): ?Subscription
    {
        return $this->repository->getSubscriptionByToken($token);
    }

    public function getSubscriptionByEmail(string $email): array
    {
        return $this->repository->getSubscriptionByEmail($email);
    }

    public function deleteSubscriptionByEmail(string $email): void
    {
        $this->repository->deleteSubscriptionByEmail($email);
    }

    public function createSubscription(NotificationType $type, array $data): void
    {
        $data['token'] = md5(uniqid());
        $data['type'] = $type->value;

        $this->repository->saveSubscription(0, $data);
    }

    public function createNewsSubscription(string $email, Language $language): void
    {
        $exists = $this->getSubscriptionByEmail($email);
        if (!empty($exists)) {
            return;
        }

        $token = md5(uniqid());

        $data = new SubscriptionDto(
            email: $email,
            language: $language->value,
            token: $token,
            type: NotificationType::News->value,
        );

        $id = $this->repository->saveSubscription(0, (array)$data);

        $this->getSubscriptionById($id)->notify(
            new NewNewsSubscriptionNotification(NotificationService::getUnsubscribeUrl($token))
        );
    }

    public function updateSubscription(int $id, array $data): void
    {
        $data['token'] = md5(uniqid());
        $this->repository->saveSubscription($id, $data);
    }

    public function deleteSubscription(string $token): void
    {
        $this->repository->deleteSubscription($token);
    }
}
