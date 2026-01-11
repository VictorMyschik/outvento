<?php

declare(strict_types=1);

namespace App\Services\User\Api;

use App\Http\Controllers\API\User\Response\UserProfileResponse;
use App\Models\User;

final readonly class UserApiResponse
{
    public function getUserResponse(User $user): UserProfileResponse
    {
        return new UserProfileResponse(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            avatar: $user->getAvatar(),
            isVerified: (bool)$user->email_verified_at,
            defaultLanguage: $user->language,
        );
    }
}