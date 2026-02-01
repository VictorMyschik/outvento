<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\FeedbackRequest;
use App\Http\Controllers\API\Request\FormRequestInterface;
use App\Services\Forms\FormService;
use Illuminate\Http\JsonResponse;

class FormsController extends APIController
{
    public function __construct(private readonly FormService $service) {}

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
