<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\API\APIController;
use App\Http\Controllers\API\Auth\Request\Auth\UpdatePasswordRequest;
use App\Http\Controllers\API\User\Request\UpdateProfileRequest;
use App\Services\System\Enum\Language;
use App\Services\User\Api\UserApiResponse;
use App\Services\User\DTO\UserProfileDTO;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UsersController extends APIController
{
    public function __construct(
        private readonly UserService       $userService,
        protected readonly UserApiResponse $response,
    ) {}

    #[OA\Get(
        path: "/api/v1/user",
        operationId: "profile",
        summary: "Получить информацию о текущем пользователе",
        security: [["bearerAuth" => []]],
        tags: ["User info"],
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
                        new OA\Property(property: "content", ref: "#/components/schemas/UserProfileResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function profile(Request $request): JsonResponse
    {
        return $this->apiResponse($this->response->getUserResponse($request->user()));
    }

    public function profileFull(Request $request): JsonResponse
    {
        return $this->apiResponse($this->response->getUserFullResponse($request->user()));
    }

    #[OA\Post(
        path: "/api/v1/user/profile/edit",
        operationId: "updateProfile",
        summary: "Обновить информацию о пользователе",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateProfileRequest")
        ),
        tags: ["User info"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "content", ref: "#/components/schemas/UserProfileResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 422, description: "Bad Request", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->userService->updateUser($request->user(), $request->getUpdateData());

        return $this->apiResponse(
            $this->response->getUserResponse($updatedUser),
        );
    }

    #[OA\Post(
        path: "/api/v1/user/password",
        operationId: "changePassword",
        summary: "Изменить пароль пользователя",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdatePasswordRequest")
        ),
        tags: ["User info"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Bad Request", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function changePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->userService->changePassword($request->user(), $request->getPassword());

        return $this->apiResponse(code: 204);
    }

    #[OA\Delete(
        path: "/api/v1/user/avatar",
        operationId: "removeAvatar",
        summary: "Удалить аватар пользователя",
        security: [["bearerAuth" => []]],
        tags: ["User info"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function removeAvatar(Request $request): JsonResponse
    {
        $this->userService->removeAvatar($request->user());

        return $this->apiResponse(code: 204);
    }

    #[OA\Post(
        path: "/api/v1/user/locale/{locale}",
        operationId: "setLocale",
        summary: "Установить локаль пользователя",
        security: [["bearerAuth" => []],
        ],
        tags: ["User info"],
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
        $userService->setLocale($request->user()->id, Language::fromCode($locale));

        return $this->apiResponse(code: 204);
    }
}
