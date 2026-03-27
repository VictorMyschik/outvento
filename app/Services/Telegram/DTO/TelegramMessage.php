<?php

declare(strict_types=1);

namespace App\Services\Telegram\DTO;

final readonly class TelegramMessage
{
    public function __construct(
        public int          $messageId,
        public TelegramUser $from,
        public string       $chatType,
        public ?string      $text,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageId: $data['message_id'],
            from: TelegramUser::fromArray($data['from']),
            chatType: $data['chat']['type'],
            text: $data['text'] ?? null,
        );
    }
}

