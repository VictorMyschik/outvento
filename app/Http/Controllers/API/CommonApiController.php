<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Response\Common\LanguagesResponse;
use App\Services\Language\API\TranslateApiService;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\System\Enum\Language;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CommonApiController extends APIController
{
    public function __construct(
        private readonly TranslateApiService $translateApiService,
    ) {}

    #[OA\Get(
        path: "/api/v1/common/languages",
        operationId: "getLanguages",
        summary: "Get available languages",
        tags: ["Common"],
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
                            ref: "#/components/schemas/LanguagesResponse",
                            type: "object"
                        ),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function getLanguages(): JsonResponse
    {
        return $this->apiResponse(
            new LanguagesResponse(Language::getCodeWithLabel()),
        );
    }

    #[OA\Get(
        path: "/api/v1/translate/common",
        operationId: "getCommonTranslate",
        summary: "Get common translations",
        tags: ["Common"],
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
                        new OA\Property(property: 'content', description: 'Localization object (key => string)', type: 'object', example: "{'login': 'Вход'}"),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function getCommonTranslate(): JsonResponse
    {
        return $this->apiResponse(
            $this->translateApiService->getTranslateFor(TranslateGroupEnum::Common, $this->getLanguage()),
        );
    }
}