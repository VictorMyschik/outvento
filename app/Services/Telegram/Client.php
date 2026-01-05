<?php

declare(strict_types=1);

namespace App\Services\Telegram;

final readonly class Client
{
    private const string TG_HOST = 'https://api.telegram.org';

    public function sendMessage(string $userId, string $message): void
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL            => self::TG_HOST . '/bot' . env('TELEGRAM_TOKEN') . '/sendMessage',
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
        $url = 'https://allximik.com/api/telegram';
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL            => self::TG_HOST . env('TELEGRAM_TOKEN') . '/setWebhook?url=' . $url,
                CURLOPT_POST           => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT        => 10,
            ]
        );

        $response = curl_exec($ch);

        return json_decode($response, true);
    }
}
