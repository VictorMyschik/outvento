<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Actions\User\YandexRegisterAction;
use App\Http\Controllers\API\APIController;
use App\Http\Controllers\API\Auth\Request\Auth\AuthenticateRequest;
use App\Http\Controllers\API\Auth\Request\Auth\ChangePasswordRequest;
use App\Http\Controllers\API\Auth\Request\Auth\RegisterRequest;
use App\Http\Controllers\API\Auth\Request\Auth\ResetPasswordRequest;
use App\Http\Controllers\API\Auth\Request\Auth\VerifyRegistrationRequest;
use App\Http\Controllers\API\Auth\Response\LoginResponse;
use App\Services\User\DTO\UserProfileDTO;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthController extends APIController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    #[OA\Post(
        path: "/api/v1/register",
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
                description: "Successful login",
                content: new OA\JsonContent(
                    required: ["status", "content"],
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok"),
                        new OA\Property(property: "content", ref: "#/components/schemas/LoginResponseContent", type: "object"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError"))
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new UserProfileDTO(
            email: $request->getEmail(),
            name: $request->getName(),
            password: Hash::make($request->getPassword()),
            language: $this->getLanguage()->value,
        );

        return $this->apiResponse(['token' => $this->userService->createWithAuth($dto)]);
    }

    #[OA\Post(
        path: "/api/v1/login",
        operationId: 'login',
        description: "Авторизация происходит по email и паролю. Успешный ответ будет содержать Bearer токен, который нужно
        использовать для авторизации в других запросах. Токен будет действителен 60 минут. Для обновления токена следует ещё раз выполнить текущий запрос.",
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
                        new OA\Property(property: "content", ref: "#/components/schemas/LoginResponseContent", type: "object"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(ref: "#/components/schemas/AuthError")),
            new OA\Response(response: 422, description: "Validation error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationError"))
        ]
    )]
    public function login(AuthenticateRequest $request): JsonResponse
    {
        return $this->apiResponse(
            new LoginResponse($this->userService->authorize($request->getLogin(), $request->getPassword(), $request->getRemember())),
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
    public function logout(): JsonResponse
    {
        Auth::user()?->currentAccessToken()->delete();

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

    #[OA\Get(
        path: "/api/v1/auth/yandex",
        operationId: 'yandexAuth',
        description: "Будет произведён редирект на страницу авторизации Яндекс. После успешной авторизации, пользователь будет перенаправлен на указанный в настройках приложения URL.",
        summary: "Yandex авторизация",
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 302,
                description: "Redirect to Yandex",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "redirect"),
                        new OA\Property(property: "url", type: "string", example: "https://oauth.yandex.ru/authorize")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function yandex(): RedirectResponse
    {
        return Socialite::driver('yandex')->redirect();
    }

    public function yandexRedirect(YandexRegisterAction $action): JsonResponse
    {
        $action->execute();

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
        $this->userService->verifyEmailAddress((int)$request->validated('code'), $request->user());

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

        $this->userService->sendVerifyNotification($request->user());

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
        $this->userService->sendResetPassword($request->validated('email'));

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
        $token = substr($token, 0, UserService::TOKEN_LENGTH);
        try {
            $this->userService->checkActualResetPasswordToken($token);
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
        $this->userService->setPasswordByCode($request->validated('token'), $request->validated('password'));

        return $this->apiResponse();
    }
}
