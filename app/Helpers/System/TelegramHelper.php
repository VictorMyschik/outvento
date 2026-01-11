<?php

declare(strict_types=1);

namespace App\Helpers\System;

use App\Http\Controllers\Controller;

class TelegramHelper extends Controller
{
    public static function sendMeByTelegram(string $text): void
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL            => 'https://api.telegram.org/bot' . env('TELEGRAM_TOKEN') . '/sendMessage',
                CURLOPT_POST           => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_POSTFIELDS     => array(
                    'chat_id' => env('TELEGRAM_CHATID'),
                    'text'    => $text,
                ),
            )
        );
        curl_exec($ch);
    }
}
