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
            defaultLanguage: $user->getLanguage()->getCode(),
            isVerified: (bool)$user->email_verified_at,
            telegram: $user->telegram_chat_id,
            firstName: '',
            lastName: '',
            gender: '',
            birthday: '',
            about: '',
        );
    }

    public function getUserFullResponse(User $user): UserProfileResponse
    {
        return new UserProfileResponse(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            avatar: $user->getAvatar(),
            defaultLanguage: $user->getLanguage()->getCode(),
            isVerified: (bool)$user->email_verified_at,
            telegram: $user->telegram_chat_id,
            firstName: $user->first_name,
            lastName: $user->last_name,
            gender: $user->getGender()?->getLabel(),
            birthday: $user->birthday?->format('Y-m-d') ?? '',
            about: $user->about,
        );
    }
}
