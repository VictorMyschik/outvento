<?php

declare(strict_types=1);

namespace App\Services\Telegram\DTO;

use App\Services\Telegram\Enum\CommandType;

final readonly class TelegramUpdate
{
    public function __construct(
        public int                    $updateId,
        public ?TelegramMessage       $message,
        public ?TelegramCallbackQuery $callbackQuery,
    ) {}

    public function isBot(): bool
    {
        return $this->message?->from?->isBot
            ?? $this->callbackQuery?->from?->isBot
            ?? false;
    }

    public function getUserId(): ?int
    {
        return $this->message?->from?->id
            ?? $this->callbackQuery?->from->id;
    }

    public function getText(): ?string
    {
        return $this->message?->text
            ?? $this->callbackQuery?->data;
    }

    public function isCommand(CommandType $command): bool
    {
        return $this->getText() !== null && str_starts_with($this->getText(), $command->value);
    }

    public function getCommand(): CommandType
    {
        return CommandType::tryFromCode($this->getText() ?? '') ?? CommandType::Unknown;
    }
}

