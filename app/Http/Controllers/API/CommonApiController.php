<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Request\TranslationRequest;
use App\Http\Controllers\API\Response\Common\ContactsResponse;
use App\Http\Controllers\API\Response\Common\LanguagesResponse;
use App\Http\Controllers\API\Response\FrontendSettingsResponse;
use App\Services\Language\API\TranslateApiService;
use App\Services\Language\Enum\TranslateGroupEnum;
use App\Services\Notifications\NotificationService;
use App\Services\Other\LegalDocuments\Enum\LegalDocumentType;
use App\Services\Other\LegalDocuments\LegalDocumentsApiService;
use App\Services\System\Enum\Language;
use App\Services\System\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CommonApiController extends APIController
{
    public function __construct(
        private readonly TranslateApiService      $translateApiService,
        private readonly SettingsService          $settingsService,
        private readonly LegalDocumentsApiService $termsAndConditionsApiService,
        private readonly NotificationService      $notificationService,
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
        path: "/api/v1/translations",
        operationId: "getTranslations",
        description: "Returns translations for specified groups in the requested language",
        summary: "Get translations",
        tags: ["I18n"],
        parameters: [
            new OA\Parameter(
                name: "groups[]",
                description: "Translation groups to load (multiple values allowed)",
                in: "query",
                required: false,
                schema: new OA\Schema(
                    type: "array",
                    items: new OA\Items(type: "string", example: "auth"),
                ),
                example: ["auth", "passwords", "validation"]
            ),
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
                            description: "Key-value pairs of translations",
                            type: "object",
                            example: [
                                "failed"   => "Эти учетные данные не соответствуют нашим записям.",
                                "password" => "Предоставленный пароль неверен.",
                                "throttle" => "Слишком много попыток входа. Пожалуйста, попробуйте еще раз через :seconds секунд.",
                            ],
                            additionalProperties: new OA\AdditionalProperties(type: "string")
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 400, description: "Bad request"),
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
        ]
    )]
    public function getTranslations(TranslationRequest $request): JsonResponse
    {
        return $this->apiResponse(
            $this->translateApiService->getTranslateFor($request->getGroups(), $this->getLanguage()),
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
                translations: $this->translateApiService->getTranslateFor([TranslateGroupEnum::Common, TranslateGroupEnum::Passwords, TranslateGroupEnum::Emails], $this->getLanguage()),
            ),
        );
    }

    #[OA\Get(
        path: "/api/v1/legal/{type}",
        operationId: "getLegalDocumentByType",
        summary: "Get legal document by type",
        tags: ["Pages"],
        parameters: [
            new OA\Parameter(name: "type", in: "path", required: true, schema: new OA\Schema(type: "string", example: "terms_and_conditions")),
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
                        new OA\Property(property: "content", ref: "#/components/schemas/TermsAndConditionsResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 404, description: "Not found"),
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
        ]
    )]
    public function legal(LegalDocumentType $type): JsonResponse
    {
        return $this->apiResponse(
            $this->termsAndConditionsApiService->getLegalDocumentByType($type, $this->getLanguage()),
        );
    }

    public function confirmNotificationToken(Request $request, string $token): JsonResponse
    {
        $this->notificationService->confirmNotificationToken(
            token: $token,
            info: [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer'    => $request->header('referer', ''),
            ]
        );

        return $this->apiResponse(code: 204);
    }
}