<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\NotificationCode;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\System\ResetPassword;
use App\Repositories\User\UserRepository;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;
use App\Services\Notifications\SystemNotificationService;
use App\Services\System\Enum\Language;
use App\Services\User\DTO\UserProfileDTO;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AuthService
{
    public const int TOKEN_LENGTH = 60;
    private const int RESET_PASSWORD_TOKEN_EXPIRY_MINUTES = 20;
    public const int ACTION_VERIFY_REG_TIME_EXPIRY_MINUTES = 20;

    public function __construct(
        private UserRepository            $repository,
        private SystemNotificationService $systemNotificationService,
    ) {}

    public function authorize(string $loginOrEmail, string $password, bool $isRememberMe): ?string
    {
        $email = $loginOrEmail;
        if (filter_var($loginOrEmail, FILTER_VALIDATE_EMAIL) === false) {
            $email = $this->repository->getEmailByName($loginOrEmail);

            if (!$email) {
                throw new AuthenticationException(__('auth.failed'));
            }
        }

        $credentials = [
            'email'    => $email,
            'password' => $password,
        ];

        if (!Auth::attempt($credentials)) {
            throw new AuthenticationException(__('auth.failed'));
        }

        /** @var User $user */
        $user = Auth::user();

        return $this->issueToken($user, $isRememberMe);
    }

    public function createWithAuth(UserProfileDTO $dto): string
    {
        $user = $this->create($dto);

        Auth::login($user, true);

        $this->sendVerifyNotification($user);

        return $this->issueToken($user);
    }

    public function sendResetPassword(string $email, Language $language): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return;
        }

        $this->sendResetPasswordNotification($user, $language);
    }

    public function sendVerifyNotification(User $user): void
    {
        $code = (string)random_int(100000, 999999);

        $notificationCode = NotificationCode::updateOrCreate(
            [
                'user_id' => $user->id,
                'type'    => SystemEvent::RegistrationConfirmation->value,
                'address' => $user->email,
                'channel' => NotificationChannel::Email->value,
            ],
            [
                'code' => $code,
            ]
        );

        $this->systemNotificationService->verifyRegistrationCode($notificationCode, self::ACTION_VERIFY_REG_TIME_EXPIRY_MINUTES);
    }

    private function sendResetPasswordNotification(User $user, Language $language): void
    {
        $token = mb_strtolower(Str::random(self::TOKEN_LENGTH));

        PasswordResetToken::updateOrCreate(['email' => $user->email], ['token' => $token]);

        $url = config('app.url') . '/forgot-password?token=' . $token;

        $user->notify(new ResetPassword($url, $language->getCode(), self::RESET_PASSWORD_TOKEN_EXPIRY_MINUTES));
    }

    public function create(UserProfileDTO $dto): User
    {
        $id = $this->repository->createUser([
            'name'               => $dto->name,
            'email'              => $dto->email,
            'password'           => Hash::make($dto->password),
            'language'           => $dto->language,
            'subscription_token' => $this->generateSubscriptionToken(),
        ]);

        $this->repository->updateUserRoles($id, $this->repository->getIdsForRoles($dto->roles));

        return $this->repository->getUserById($id);
    }

    public function generateSubscriptionToken(): string
    {
        return Str::random(32);
    }

    public function checkActualResetPasswordToken(string $token): void
    {
        $datetime = now()->subMinutes(self::RESET_PASSWORD_TOKEN_EXPIRY_MINUTES);

        PasswordResetToken::where('token', $token)
            ->where(function ($q) use ($datetime) {
                $q->where(fn($q) => $q->where('created_at', '>=', $datetime)->whereNull('updated_at'))
                    ->orWhere(fn($q) => $q->where('updated_at', '>=', $datetime));
            })
            ->firstOrFail();
    }

    public function setPasswordByCode(string $token, string $password): void
    {
        try {
            $datetime = now()->subMinutes(self::RESET_PASSWORD_TOKEN_EXPIRY_MINUTES);

            $passwordResetToken = PasswordResetToken::where('token', $token)
                ->where(function ($q) use ($datetime) {
                    $q->where(fn($q) => $q->where('created_at', '>=', $datetime)->whereNull('updated_at'))
                        ->orWhere(fn($q) => $q->where('updated_at', '>=', $datetime));
                })
                ->firstOrFail();;
        } catch (\Exception $e) {
            throw new NotFoundHttpException(__('mr-t.reset_password_token_invalid'));
        }

        $user = User::where('email', $passwordResetToken->email)->firstOrFail();
        $user->password = Hash::make($password);
        $user->save();

        $passwordResetToken->delete();
    }

    public function verifyEmailAddress(int $code, User $user): void
    {
        $notification = NotificationCode::where([
            'user_id' => $user->id,
            'code'    => $code,
            'action'  => NotificationCode::ACTION_VERIFY_REG,
        ])->first();

        if (!$notification) {
            throw new NotFoundHttpException(__('auth.verification_not_found'));
        }

        $user->markEmailAsVerified();

        $notification->delete();
    }

    public function changePassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();
    }

    private function issueToken(User $user, bool $isRememberMe = false): string
    {
        return $user->createToken(name: 'auth-token',
            expiresAt: $isRememberMe ? now()->addDays(7) : now()->addDay(),
        )->plainTextToken;
    }
}