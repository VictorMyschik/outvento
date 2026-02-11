<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\API\APIController;
use App\Http\Controllers\API\User\Request\CommunicationRequest;
use App\Http\Controllers\API\User\Request\UpdateProfileRequest;
use App\Models\User;
use App\Services\User\Api\UserApiResponse;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

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
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->userService->updateUser($request->user(), $request->getUpdateData());

        return $this->apiResponse(
            $this->response->getUserFullResponse($updatedUser),
        );
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

    #[OA\Get(
        path: "/api/v1/user/communications",
        operationId: "getCommunications",
        summary: "Получить список способов связи с пользователя",
        security: [["bearerAuth" => []]],
        tags: ["User info"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
            new OA\Parameter(name: "X-Locale", description: "Locale", in: "header", required: true, schema: new OA\Schema(type: "string", example: "en")),
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
                            items: new OA\Items(
                                ref: "#/components/schemas/UserCommunicationComponent",
                                type: "object"
                            )
                        )
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function getCommunications(Request $request): JsonResponse
    {
        return $this->apiResponse(
            $this->response->getCommunicationsList(
                $this->userService->getCommunications($request->user()->id, $this->getLanguage()),
            ),
        );
    }

    #[OA\Post(
        path: "/api/v1/user/communications",
        operationId: "createCommunication",
        summary: "Создать способ связи пользователя",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CommunicationRequest")
        ),
        tags: ["User info"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 201, description: "Created", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function createCommunication(CommunicationRequest $request): JsonResponse
    {
        $data = $request->getUpdateData();
        $data['user_id'] = $request->user()->id;

        $this->userService->saveCommunication(0, $data);

        return $this->apiResponse(code: 201);
    }

    #[OA\Put(
        path: "/api/v1/user/communications/{id}",
        operationId: "updateCommunication",
        summary: "Обновить способ связи пользователя",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/CommunicationRequest")
        ),
        tags: ["User info"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
            new OA\Parameter(name: "id", description: "Communication id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function updateCommunication(CommunicationRequest $request, int $id): JsonResponse
    {
        $data = $request->getUpdateData();
        $data['user_id'] = $request->user()->id;
        $this->userService->saveCommunication($id, $data);

        return $this->apiResponse(code: 204);
    }

    #[OA\Delete(
        path: "/api/v1/user/communications/{id}",
        operationId: "deleteCommunication",
        summary: "Удалить способ связи пользователя",
        security: [["bearerAuth" => []]],
        tags: ["User info"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
            new OA\Parameter(name: "id", description: "Communication id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function deleteCommunication(Request $request, int $id): JsonResponse
    {
        $this->userService->deleteCommunication($request->user()->id, $id);

        return $this->apiResponse(code: 204);
    }

    public function getUserAvatar(User $user): Response
    {
        return response()->file(Storage::disk('users')->path($user->avatar));
    }
}
