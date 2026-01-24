<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Response\WelcomeResponse;
use App\Services\Language\API\TranslateApiService;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Language\TranslateService;
use App\Services\References\API\ReferenceApiService;
use App\Services\Travel\Api\TravelApiService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class WelcomeController extends APIController
{
    public function __construct(
        private readonly TranslateApiService $translateApiService,
        private readonly TravelApiService    $apiService,
        private readonly ReferenceApiService $referenceApiService,
    ) {}

    #[OA\Get(
        path: "/api/v1/pages/welcome",
        operationId: 'welcome',
        description: 'Главная страница сайта',
        summary: "Welcome",
        tags: ["Pages"],
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
                        new OA\Property(property: "content", ref: "#/components/schemas/WelcomeResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $language = $this->getLanguage();

        return $this->apiResponse(
            new WelcomeResponse(
                countries: $this->referenceApiService->getUsingCountrySelectList($language),
                translations: $this->translateApiService->getTranslateFor([TranslateGroupEnum::PageWelcome], $language),
                travelTypeList: $this->referenceApiService->getTravelTypeList($language),
                travelExamples: $this->apiService->travelExamples($language),
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
