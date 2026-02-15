<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\PromoSubscriptionRequest;
use App\Services\Notifications\Enum\PromoEvent;
use App\Services\Promo\DTO\SubscriptionDto;
use App\Services\Promo\Enum\PromoSource;
use App\Services\Promo\Enum\Status;
use App\Services\Promo\SubscriptionService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends APIController
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function subscribe(PromoSubscriptionRequest $request): JsonResponse
    {
        $this->subscriptionService->createSubscriptionWithNotify(
            new SubscriptionDto(
                email: $request->getEmail(),
                language: $this->getLanguage(),
                event: PromoEvent::News,
                source: PromoSource::from($request->input('source'))
            )
        );

        return $this->apiResponse(code: 201);
    }

    public function confirm(string $token): JsonResponse
    {
        $subscription = $this->subscriptionService->getSubscriptionByToken($token);

        if (!$subscription || $subscription->getStatus() !== Status::Pending) {
            return $this->apiResponse(code: 404);
        }

        $this->subscriptionService->confirmSubscription($subscription);

        return $this->apiResponse(code: 204);
    }
}