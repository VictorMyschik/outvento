<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\SubscriptionRequest;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SubscriptionApiController extends APIController
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    #[OA\Get(
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
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function subscribe(SubscriptionRequest $request): JsonResponse
    {
        $this->subscriptionService->createNewsSubscription($request->getEmail(), $this->getLanguage());

        return $this->apiResponse(code: 204);
    }
}
