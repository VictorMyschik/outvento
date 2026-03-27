<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ORM\ORM;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NotificationCode extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'notification_codes';

    public const null UPDATED_AT = null;
    public const string ACTION_VERIFY_REG = 'verify-registration';

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'channel',
        'address',
        'data',
    ];

    protected array $allowedSorts = [
        'id',
        'user_id',
        'code',
        'channel',
        'address',
        'type',
        'data',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getType(): SystemEvent
    {
        return SystemEvent::from($this->type);
    }

    public function getChannel(): NotificationChannel
    {
        return NotificationChannel::from($this->channel);
    }

    public static function getSelectList(): array
    {
        return [
            self::ACTION_VERIFY_REG => 'Verify Registration',
        ];
    }

    public function getUser(): User
    {
        return User::findOrFail($this->user_id);
    }
}
