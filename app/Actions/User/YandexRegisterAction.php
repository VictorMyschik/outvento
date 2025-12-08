<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Services\User\UserService;
use Laravel\Socialite\Facades\Socialite;

readonly class YandexRegisterAction
{
    public function __construct(private UserService $userService) {}

    public function execute(): void
    {
        $this->userService->registerWithYandex(
            Socialite::driver('yandex')->user()
        );
    }
}
