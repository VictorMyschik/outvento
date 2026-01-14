<?php

declare(strict_types=1);

namespace App\Services\User\DTO;

final readonly class UserProfileDTO
{
    public function __construct(
        public ?string $email,
        public ?string $name,
        public ?string $password = null,
        public int     $language,
    ) {}
}
