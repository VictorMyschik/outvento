<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Services\Telegram\DTO\TelegramCallbackQuery;
use App\Services\Telegram\DTO\TelegramMessage;
use App\Services\Telegram\DTO\TelegramUpdate;
use App\Services\Telegram\Exceptions\BotMessageNotAllowed;
use Illuminate\Http\Request;

final class TelegramUpdateFactory
{
    public static function fromRequest(Request $request): TelegramUpdate
    {
        $data = $request->json()->all();

        if (($data['message']['from']['is_bot'] ?? false) === true) {
            throw new BotMessageNotAllowed();
        }

        return new TelegramUpdate(
            updateId: $data['update_id'],
            message: isset($data['message'])
                ? TelegramMessage::fromArray($data['message'])
                : null,
            callbackQuery: isset($data['callback_query'])
                ? TelegramCallbackQuery::fromArray($data['callback_query'])
                : null,
        );
    }
}

