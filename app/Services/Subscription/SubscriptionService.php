<?php

declare(strict_types=1);

namespace App\Services\Subscription;

use App\Models\Subscription\Subscription;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Subscription\DTO\SubscriptionDto;
use App\Services\System\Enum\Language;

final readonly class SubscriptionService
{
    public function __construct(private SubscriptionRepositoryInterface $repository) {}

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

    public function createSubscription(EmailTypeEnum $type, array $data): int
    {
        $data['token'] = md5(uniqid());
        $data['type'] = $type->value;

        return $this->repository->saveSubscription(0, $data);
    }

    public function createNewsSubscription(string $email, Language $language): int
    {
        $exists = $this->getSubscriptionByEmail($email);
        if (!empty($exists)) {
            return 0;
        }

        $data = new SubscriptionDto(
            email: $email,
            language: $language->value,
            token: md5(uniqid()),
            type: EmailTypeEnum::NEWS->value,
        );

        return $this->repository->saveSubscription(0, (array)$data);
    }

    public function updateSubscription(int $id, array $data): int
    {
        $data['token'] = md5(uniqid());
        return $this->repository->saveSubscription($id, $data);
    }

    public function deleteSubscription(string $token): void
    {
        $this->repository->deleteSubscription($token);
    }

    public function getListTo(EmailTypeEnum $type, Language $language): array
    {
        return $this->repository->getListEmailsByType($type, $language);
    }
}
