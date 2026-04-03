<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\APIController;
use App\Http\Controllers\API\Auth\Request\Auth\AuthenticateRequest;
use App\Http\Controllers\API\Auth\Request\Auth\ChangePasswordRequest;
use App\Http\Controllers\API\Auth\Request\Auth\RefreshTokenRequest;
use App\Http\Controllers\API\Auth\Request\Auth\RegisterRequest;
use App\Http\Controllers\API\Auth\Request\Auth\ResetPasswordRequest;
use App\Http\Controllers\API\Auth\Request\Auth\UpdatePasswordRequest;
use App\Http\Controllers\API\Auth\Request\Auth\VerifyRegistrationRequest;
use App\Http\Controllers\API\Auth\Response\AuthTokenResponse;
use App\Services\User\AuthService;
use App\Services\User\DTO\UserProfileDTO;
use App\Services\User\Enum\UserRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthController extends APIController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    #[OA\Post(
        path: "/api/v1/register",
        operationId: 'register',
        description: "Регистрация нового пользователя и возвращает access и refresh токенов. Успешный ответ будет содержать Bearer токен, который нужно использовать для авторизации в других запросах.",
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
                response: 201,
                description: "Successful login",
                content: new OA\JsonContent(
                    required: ["status", "content"],
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "content", ref: "#/components/schemas/AuthTokenResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError"))
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->authService->createWithAuth(new UserProfileDTO(
            email: $request->getEmail(),
            name: $request->getName(),
            password: $request->getPassword(),
            language: $this->getLanguage()->value,
            roles: [UserRole::User],
        ), $request->remember());

        return $this->apiResponse(
            new AuthTokenResponse(
                accessToken: $data['accessToken'],
                refreshToken: $data['refreshToken'],
                tokenType: $data['tokenType'],
                expiresIn: $data['expiresIn'],
            ),
            201,
        );
    }

    #[OA\Post(
        path: "/api/v1/login",
        operationId: 'login',
        description: "Access token имеет ограниченный срок действия. Для его обновления используйте endpoint /refresh с refresh token.",
        summary: "Login",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/AuthenticateRequest")
        ),
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful login",
                content: new OA\JsonContent(
                    required: ["status", "content"],
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "content", ref: "#/components/schemas/AuthTokenResponse", type: "object"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError"))
        ]
    )]
    public function login(AuthenticateRequest $request): JsonResponse
    {
        $data = $this->authService->authorize($request->getLogin(), $request->getPassword(), $request->getRemember());

        return $this->apiResponse(
            new AuthTokenResponse(
                accessToken: $data['accessToken'],
                refreshToken: $data['refreshToken'],
                tokenType: $data['tokenType'],
                expiresIn: $data['expiresIn'],
            ),
        );
    }

    #[OA\Post(
        path: "/api/v1/refresh",
        operationId: 'refresh',
        description: "Обновление access token с помощью refresh token.",
        summary: "Refresh token",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/RefreshTokenRequest")
        ),
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful refresh",
                content: new OA\JsonContent(
                    required: ["status", "content"],
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "content", ref: "#/components/schemas/AuthTokenResponse"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Invalid token"),
        ]
    )]
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $data = $this->authService->refresh(
            $request->getRefreshToken(),
        );

        return $this->apiResponse(
            new AuthTokenResponse(
                accessToken: $data['accessToken'],
                refreshToken: $data['refreshToken'],
                tokenType: $data['tokenType'],
                expiresIn: $data['expiresIn'],
            ),
        );
    }

    #[OA\Post(
        path: "/api/v1/logout",
        operationId: 'logout',
        description: "Выход из системы. Удаляет текущий токен доступа пользователя.",
        summary: "Logout",
        security: [["bearerAuth" => []]],
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError"))
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (!$user) {
            throw new AuthenticationException('unauthorized');
        }

        $accessToken = $request->bearerToken();

        if ($accessToken) {
            $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($accessToken);

            if ($tokenModel) {
                $tokenModel->delete();
            }
        }

        return $this->apiResponse();
    }

    #[OA\Post(
        path: "/api/v1/logout-all",
        operationId: 'logoutAllSessions',
        description: "Выход из системы на всех устройствах. Удаляет все токены доступа пользователя.",
        summary: "Logout для всех устройств",
        security: [["bearerAuth" => []]],
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError"))
        ]
    )]
    public function logoutAllSessions(): JsonResponse
    {
        Auth::user()?->tokens()->delete();

        return $this->apiResponse();
    }

    #[OA\Post(
        path: "/api/v1/user/verify",
        operationId: 'verifyRegistration',
        description: "Подтверждение регистрации пользователя с помощью кода верификации.",
        summary: "Подтверждение регистрации",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/VerifyRegistrationRequest")
        ),
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 404, description: "Not Found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError"))
        ]
    )]
    public function verifyRegistration(VerifyRegistrationRequest $request): JsonResponse
    {
        $this->authService->verifyEmailAddress((int)$request->validated('code'), $request->user());

        return $this->apiResponse(code: 204);
    }

    #[OA\Post(
        path: "/api/v1/user/verify/resend",
        operationId: 'verifyResend',
        description: "Повторная отправка кода верификации на email пользователя. Код будет отправлен на email, указанный при регистрации.",
        summary: "Повторная отправка кода верификации",
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function verifyResend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            throw new LogicException('Текущий аккаунт уже подтвержден');
        }

        $this->authService->sendVerifyNotification($request->user());

        return $this->apiResponse(code: 204);
    }

    #[OA\Post(
        path: "/api/v1/reset-password",
        operationId: 'resetPasswordToken',
        description: "Отправка ссылки для сброса пароля на email пользователя.",
        summary: "Отправка ссылки для сброса пароля",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/ResetPasswordRequest")
        ),
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 404, description: "Not Found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError"))
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->sendResetPassword($request->validated('email'), $this->getLanguage());

        return $this->apiResponse();
    }

    #[OA\Post(
        path: "/api/v1/reset-password/{token}/check",
        operationId: "checkResetPasswordToken",
        description: "Проверка актуальности токена для сброса пароля. Возвращает 204, если токен действителен.",
        summary: "Проверка токена сброса пароля",
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
            new OA\Parameter(name: "token", description: "Токен сброса пароля", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError"))
        ]
    )]
    public function checkActualResetPasswordToken(string $token): JsonResponse
    {
        // только цифры и буквы
        $token = preg_replace("/[^a-zA-Z0-9]/", "", $token);
        if (strlen($token) !== AuthService::TOKEN_LENGTH) {
            throw new AccessDeniedHttpException(__('mr-t.reset_password_token_invalid'));
        }

        try {
            $this->authService->checkActualResetPasswordToken($token);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException(__('mr-t.reset_password_token_invalid'));
        }

        return $this->apiResponse(code: 204);
    }

    #[OA\Post(
        path: "/api/v1/reset-password/change",
        operationId: 'resetPasswordConfirm',
        description: "Изменение пароля пользователя с использованием ссылки сброса.",
        summary: "Изменение пароля",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/ChangePasswordRequest")
        ),
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 404, description: "Not Found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundError"))
        ]
    )]
    public function resetPasswordConfirm(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->setPasswordByCode($request->validated('token'), $request->validated('password'));

        return $this->apiResponse();
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
        tags: ["Auth"],
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/XRequestedWithHeader"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Successful", content: new OA\JsonContent(ref: "#/components/schemas/SuccessfulEmptyResponse")),
            new OA\Response(response: 422, description: "Unprocessable Entity", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
        ]
    )]
    public function changePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->getPassword());

        return $this->apiResponse(code: 204);
    }
}
