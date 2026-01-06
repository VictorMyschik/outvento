<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use App\Services\User\UserService;

readonly class RemoveAvatarAction
{
    public function __construct(private UserService $userService) {}

    public function execute(User $user): void
    {
        $this->userService->removeAvatar($user);
    }
}
