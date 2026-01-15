<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\NotificationCode;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyRegistrationCode;
use App\Repositories\User\UserRepository;
use App\Services\System\Enum\Language;
use App\Services\Upload\UploadService;
use App\Services\User\DTO\UserProfileDTO;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UserService
{
    public const int TOKEN_LENGTH = 60;
    private const int RESET_PASSWORD_TOKEN_EXPIRY_MINUTES = 20;
    private const int ACTION_VERIFY_REG_TIME_EXPIRY_MINUTES = 20;

    private Language $language;

    public function __construct(
        private UploadService  $uploadService,
        private UserRepository $repository,
    )
    {
        $this->language = Language::fromCode(app()->getLocale());
    }

    public function authorize(string $email, string $password, bool $isRememberMe): ?string
    {
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

    public function create(UserProfileDTO $dto): User
    {
        $user = new User();

        $user->fill([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password,
            'language' => $dto->language,
        ])->save();

        return $user;
    }

    public function registerWithYandex(object $user): string
    {
        $user = User::firstOrCreate([
            'email' => $user->email
        ], [
            'first_name' => $user->user['display_name'],
            'password'   => Hash::make(Str::random(24)),
        ]);

        Auth::login($user, true);

        return $this->issueToken($user);
    }

    private function issueToken(User $user, bool $isRememberMe = false): string
    {
        return $user->createToken(name: 'auth-token',
            expiresAt: $isRememberMe ? now()->addDays(7) : now()->addDay(),
        )->plainTextToken;
    }

    public function updateUser(User $user, array $data): User
    {
        if (!empty($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;

            $this->sendVerifyNotification($user);
        }

        if (count($data)) {
            $this->repository->updateUser($user->id, $data);
        }

        return $this->repository->getUserById($user->id);
    }

    public function verifyEmailAddress(int $code, User $user): void
    {
        $notification = NotificationCode::where([
            'user_id' => $user->id(),
            'code'    => $code,
            'action'  => NotificationCode::ACTION_VERIFY_REG,
        ])->first();

        if (!$notification) {
            throw new NotFoundHttpException('Информация не найдена, проверьте правильность ввода данных или запросите новый код подтверждения.');
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

    public function sendVerifyNotification(User $user): void
    {
        $code = (string)rand(100000, 999999);

        NotificationCode::updateOrCreate(
            [
                'user_id' => $user->id,
                'action'  => NotificationCode::ACTION_VERIFY_REG,
            ],
            [
                'code' => $code,
            ]
        );

        $user->notify(new VerifyRegistrationCode($code, self::ACTION_VERIFY_REG_TIME_EXPIRY_MINUTES));
    }

    public function sendResetPassword(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return;
        }

        $this->sendResetPasswordNotification($user);
    }

    public function checkActualResetPasswordToken(string $token): void
    {
        $datetime = now()->subMinutes(self::RESET_PASSWORD_TOKEN_EXPIRY_MINUTES);

        PasswordResetToken::where('token', $token)
            ->where(function ($query) use ($datetime) {
                $query->where('created_at', '>=', $datetime)->whereNull('updated_at');
            })
            ->orWhere(function ($query) use ($datetime) {
                $query->where('updated_at', '>=', $datetime);
            })
            ->firstOrFail();
    }

    public function setPasswordByCode(string $token, string $password): void
    {
        try {
            $datetime = now()->subMinutes(self::RESET_PASSWORD_TOKEN_EXPIRY_MINUTES);

            $passwordResetToken = PasswordResetToken::where('token', $token)
                ->where(function ($query) use ($datetime) {
                    $query->where('created_at', '>=', $datetime)->whereNull('updated_at');
                })
                ->orWhere(function ($query) use ($datetime) {
                    $query->where('updated_at', '>=', $datetime);
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

    private function sendResetPasswordNotification(User $user): void
    {
        $token = mb_strtolower(Str::random(self::TOKEN_LENGTH));

        PasswordResetToken::updateOrCreate(['email' => $user->email], ['token' => $token]);

        $url = config('app.url') . '/reset-password?token=' . $token;

        $user->notify(new ResetPassword($url, $this->language->getCode(), self::RESET_PASSWORD_TOKEN_EXPIRY_MINUTES));
    }

    public function removeAvatar(User $user): void
    {
        if (!is_null($user->getAvatar())) {
            $this->uploadService->deleteFile($user->getAvatar());
            $user->avatar = null;
            $user->save();
        }
    }

    public function deleteUser(User $user): void
    {
        $this->repository->deleteCommunicates($user->id);
        $this->removeAvatar($user);
        $user->tokens()->delete();
        $user->softDelete();
    }

    public function saveCommunicate(int $id, array $data): void
    {
        $this->repository->saveCommunicate($id, $data);
    }

    public function getCommunicates(User $user, Language $language): array
    {
        return $this->repository->getCommunicates($user->id, $language);
    }

    public function deleteCommunication(User $user, int $id): void
    {
        $this->repository->deleteCommunicate($user->id, $id);
    }
}
