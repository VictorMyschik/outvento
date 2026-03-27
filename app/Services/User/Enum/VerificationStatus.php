<?php

declare(strict_types=1);

namespace App\Services\User\Enum;

enum VerificationStatus: int
{
    case NotVerified = 0;
    case Verified = 1;

    public function getLabel(): string
    {
        return __('enums.verification_status.' . $this->name);
    }

    public static function getSelectList(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }

    public function isVerified(): bool
    {
        return $this === self::Verified;
    }
}
