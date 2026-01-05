<?php

declare(strict_types=1);

namespace App\Models\MessageLog;

use App\Models\ORM\ORM;
use App\Services\Telegram\Enum\TypeEnum;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TelegramLog extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'telegram_logs';

    public const null UPDATED_AT = null;

    public array $allowedSorts = [
        'id',
        'type',
        'user_id',
        'user_tg',
        'message',
        'created_at',
    ];

    public $casts = [
        'user_id'    => 'integer',
        'message'    => 'string',
        'created_at' => 'datetime',
    ];

    public function getType(): TypeEnum
    {
        return TypeEnum::from((int)$this->type);
    }
}