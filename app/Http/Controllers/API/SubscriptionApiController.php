<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\SubscriptionRequest;
use App\Services\Notifications\SubscriptionService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SubscriptionApiController extends APIController
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    #[OA\Post(
        path: "/api/v1/subscription/subscribe",
        operationId: "subscribe",
        summary: "Subscribe to news",
        tags: ["Subscription"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Bad Request", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
        ]
    )]
    public function subscribe(SubscriptionRequest $request): JsonResponse
    {
        $this->subscriptionService->createNewsSubscription($request->getEmail(), $this->getLanguage());

        return $this->apiResponse(code: 204);
    }

    #[OA\Get(
        path: "/api/v1/subscription/subscribe/{token}",
        operationId: "unsubscribe",
        summary: "Unsubscribe for type",
        tags: ["Subscription"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
            new OA\Parameter(name: "token", description: "Subscription token", in: "path", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Bad Request", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
        ]
    )]
    public function unsubscribe(string $token): JsonResponse
    {
        $this->subscriptionService->deleteSubscription($token);

        return $this->apiResponse(code: 204);
    }
}
