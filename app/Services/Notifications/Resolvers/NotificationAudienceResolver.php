<?php

declare(strict_types=1);

namespace App\Services\Notifications\Resolvers;

use App\Models\User;
use App\Services\Notifications\Enum\NotificationAudience;

final class NotificationAudienceResolver
{
    /**
     * @param User $user
     * @return NotificationAudience[]
     */
    public static function fromUser(User $user): array
    {
        return $user->getRoles()
            ->pluck('slug')
            ->map(fn(string $slug) => NotificationAudience::tryFrom($slug))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
