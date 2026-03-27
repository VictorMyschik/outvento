<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\FeedbackRequest;
use App\Http\Controllers\API\Request\FormRequestInterface;
use App\Services\Forms\FormService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class FormsController extends APIController
{
    public function __construct(private readonly FormService $service) {}

    #[OA\Post(
        path: "/api/v1/form/feedback",
        operationId: "submitFeedback",
        description: "Submit feedback form",
        summary: "Submit feedback",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: "#/components/schemas/FeedbackRequest")),
        tags: ["Forms"],
        responses: [
            new OA\Response(response: 201, description: "Created", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
        ]
    )]
    public function feedback(FeedbackRequest $request): JsonResponse
    {
        $this->run($request);

        return $this->apiResponse(code: 201);
    }

    private function run(FormRequestInterface $request): void
    {
        $this->service->addForm($request->getDto($this->getLanguage()));
    }
}
