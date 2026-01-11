<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Response\Common\ContactsResponse;
use App\Http\Controllers\API\Response\Common\LanguagesResponse;
use App\Http\Controllers\API\Response\FrontendSettingsResponse;
use App\Services\Language\API\TranslateApiService;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\System\Enum\Language;
use App\Services\System\SettingsService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CommonApiController extends APIController
{
    public function __construct(
        private readonly TranslateApiService $translateApiService,
        private readonly SettingsService     $settingsService,
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
                        new OA\Property(property: "content", ref: "#/components/schemas/LanguagesResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function getLanguages(): JsonResponse
    {
        return $this->apiResponse(
            new LanguagesResponse(...Language::getCodeWithLabel()),
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

    #[OA\Get(
        path: "/api/v1/frontend/settings",
        operationId: "getFrontendSettings",
        summary: "Get frontend settings",
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
                        new OA\Property(property: "content", ref: "#/components/schemas/FrontendSettingsResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
        ]
    )]
    public function getFrontendSettings(): JsonResponse
    {
        return $this->apiResponse(
            new FrontendSettingsResponse(
                languages: new LanguagesResponse(...Language::getCodeWithLabel()),
                contacts: new ContactsResponse(...$this->settingsService->getContacts()),
            ),
        );
    }
}