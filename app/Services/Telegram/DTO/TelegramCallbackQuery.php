<?php

declare(strict_types=1);

namespace App\Services\Telegram\DTO;

final readonly class TelegramCallbackQuery
{
    public function __construct(
        public string       $id,
        public TelegramUser $from,
        public string       $data,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            from: TelegramUser::fromArray($data['from']),
            data: $data['data'],
        );
    }
}

