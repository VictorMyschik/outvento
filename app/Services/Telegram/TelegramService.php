<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Services\Telegram\DTO\TelegramUpdate;
use App\Services\Telegram\Enum\CommandType;
use App\Services\User\Enum\VerificationStatus;
use App\Services\User\UserService;

final readonly class TelegramService
{
    public function __construct(
        private Client      $client,
        private UserService $userService,
    ) {}

    public function handle(TelegramUpdate $data): void
    {
        $messageType = CommandType::tryFromCode($data->message->text);

        match ($messageType) {
            CommandType::Start => $this->applyNewUser($data),
            default => null,
        };
    }

    public function sendRawMessage(int $user, string $message): void
    {
        $this->client->sendMessage($user, $message);
    }

    private function applyNewUser(TelegramUpdate $data): void
    {
        $token = str_replace('/start connect_', '', $data->getText());
        $communication = $this->userService->getCommunicationByToken($token);

        if (!$communication) {
            return;
        }

        $this->userService->saveCommunicationManually($communication->id, [
            'address_ext'         => $data->getUserId(),
            'verification_status' => VerificationStatus::Verified->value,
        ]);
    }
}
