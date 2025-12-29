<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\WelcomeResponse;
use App\Services\Language\TranslateService;
use App\Services\Travel\Api\TravelApiService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
class WelcomeController extends APIController
{
    public function __construct(
        private readonly TranslateService $translateService,
        private readonly TravelApiService $service,
    ) {}

    #[OA\Post(
        path: "/api/v1/pages/welcome",
        operationId: 'register',
        description: "Регистрация нового пользователя. Успешный ответ будет содержать Bearer токен, который нужно использовать для авторизации в других запросах.",
        summary: "Регистрация",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/RegisterRequest")
        ),
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    required: ["status", "content"],
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "content", ref: "#/components/schemas/LoginResponseContent", type: "object"),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        return $this->apiResponse(
            new WelcomeResponse(
                lang: $this->translateService->getTranslateFor(),
                travelTypeList: $this->service->getTravelTypeList($this->getLanguage()),
                travelExamples: $this->service->travelExamples($this->getLanguage()),
            ),
        );
    }

    public function searchTravelPage(): View|Application|Factory
    {
        $out = [
            'lang' => TranslateService::getFullList($this->getLanguage()),
        ];

        return View('search_page')->with($out);
    }
}
