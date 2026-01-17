<?php

namespace App\Services\Telegram\Exceptions;

use RuntimeException;

final class BotMessageNotAllowed extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Messages from bots are not allowed', 403);
    }
}
