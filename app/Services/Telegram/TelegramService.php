<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Services\Telegram\DTO\TelegramUpdate;
use App\Services\Telegram\Enum\CommandType;

final readonly class TelegramService
{
    public function __construct(
        private Client $client,
    ) {}

    public function handle(TelegramUpdate $update): void
    {
        if ($update->isCommand(CommandType::Start)) {
            $token = str_replace('/start connect_', '', $update->getText());
            $this->applyNewUser($token);
        }
    }

    public function sendRawMessage(int $user, string $message): void
    {
        $this->client->sendMessage($user, $message);
    }

    private function applyNewUser(string $token): void
    {
        //  $user =
    }

    public function manageBot(int $user, string $message): void
    {
        $messageType = CommandType::tryFromCode($message);

        match ($messageType) {
            CommandType::Start => $this->client->sendMessage($user, 'Hello! I am a bot! Send me a link of OLX site to the offer.'),
            CommandType::HELP => $this->client->sendMessage($user, 'Commands: '),
            default => null,
        };
    }

    private function buildMessage(string $jsonData)//: string
    {
        /* $rows['Title:'] = $extractor->getTitle();
         $rows['Price:'] = $extractor->getPrice();

         foreach ($this->getParameters($type) as $param) {
             $rows[$param->getLabel()] = $extractor->getParameter($param->value);
         }

         $rows['URL:'] = $extractor->getLink();

         $out = '';
         foreach ($rows as $key => $item) {
             $out .= $key . ': ' . $item . "\n";
         }

         return $out;*/
    }
}
