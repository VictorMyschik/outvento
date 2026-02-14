<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\ORM\ORM;
use App\Services\Notifications\Enum\ServiceEvent;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NotificationMute extends ORM
{
    use AsSource;
    use Filterable;

    protected $table = 'notification_mutes';

    public array $allowedSorts = [
        'user_id',
        'event',
        'created_at',
    ];

    public $casts = [
        'event'      => 'int',
        'created_at' => 'datetime',
    ];

    public function getEvent(): ServiceEvent
    {
        return ServiceEvent::from((int)$this->event);
    }
}