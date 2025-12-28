<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Response\Common\LanguagesResponse;
use App\Services\System\Enum\Language;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CommonApiController extends APIController
{
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

    #[OA\Post(
        path: "/api/v1/locale/{locale}",
        operationId: "setLocale",
        summary: "Установить локаль пользователя",
        security: [
            [], // гость
            ["bearerAuth" => []], // авторизованный
        ],
        tags: ["Common"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
            new OA\Parameter(
                name: "locale",
                description: "Код локали (например: en, ru)",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "en")
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Bad Request", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
        ]
    )]
    public function setLocale(Request $request, UserService $userService, string $locale): JsonResponse
    {
        $userService->setLocale($request->user()?->id, Language::fromCode($locale));

        return $this->apiResponse(code: 204);
    }
}