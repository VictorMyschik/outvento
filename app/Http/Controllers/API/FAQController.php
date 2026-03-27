<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\FAQSearchRequest;
use App\Services\Other\Faq\Api\FaqApiService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class FAQController extends APIController
{
    public function __construct(
        private FaqApiService $apiService,
    ) {}

    #[OA\Post(
        path: "/api/v1/faq/search",
        operationId: "searchFaqs",
        summary: "Search FAQs",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/FAQSearchRequest")),
        tags: ["Pages"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful response",
                content: new OA\JsonContent(
                    required: ["status", "content"],
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "content", type: "array", items: new OA\Items(ref: "#/components/schemas/FaqComponent")),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 400, description: "Bad request"),
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
        ]
    )]
    public function search(FAQSearchRequest $request): JsonResponse
    {
        return $this->apiResponse(
            $this->apiService->searchFaqs($request->getSearchQuery(), $this->getLanguage())
        );
    }

    #[OA\Get(
        path: "/api/v1/faq/list",
        operationId: "getBaseFaqList",
        summary: "Get base FAQs",
        tags: ["Pages"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful response",
                content: new OA\JsonContent(
                    required: ["status", "content"],
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(
                            property: "content",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/FaqComponent")
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 400, description: "Bad request"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function getBaseFaqList(): JsonResponse
    {
        return $this->apiResponse(
            $this->apiService->getBaseFaqs($this->getLanguage())
        );
    }
}
