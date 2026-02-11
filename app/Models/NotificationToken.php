<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ORM\ORM;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\Enum\NotificationChannel;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NotificationToken extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'notification_tokens';

    protected $fillable = [
        'address',
        'channel',
        'type',
        'token',
        'sl',
        'created_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
    ];

    public function getType(): EventType
    {
        return EventType::from($this->type);
    }

    public function getChannel(): NotificationChannel
    {
        return NotificationChannel::from($this->channel);
    }
}
