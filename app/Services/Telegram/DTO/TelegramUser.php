<?php

declare(strict_types=1);

namespace App\Services\Telegram\DTO;

final readonly class TelegramUser
{
    public function __construct(
        public int     $id,
        public bool    $isBot,
        public string  $firstName,
        public ?string $username,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            isBot: $data['is_bot'],
            firstName: $data['first_name'],
            username: $data['username'] ?? null,
        );
    }
}

