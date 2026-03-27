<?php

declare(strict_types=1);

namespace App\Services\Telegram;

final readonly class Client
{
    public function __construct(
        private string $token,
    ) {}

    private const string TG_HOST = 'https://api.telegram.org';

    public function sendMessage(int $userId, string $message): void
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL            => self::TG_HOST . '/bot' . $this->token . '/sendMessage',
                CURLOPT_POST           => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_POSTFIELDS     => [
                    'chat_id'    => $userId,
                    'text'       => $message,
                    'parse_mode' => 'HTML',
                ],
            ]
        );
        curl_exec($ch);
    }

    public function setWebHook(): array
    {
        $url = 'https://travel.allximik.com/api/v1/telegram';
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL            => self::TG_HOST . '/bot' . $this->token . '/setWebhook?url=' . $url,
                CURLOPT_POST           => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT        => 10,
            ]
        );

        $response = curl_exec($ch);

        return json_decode($response, true);
    }
}
