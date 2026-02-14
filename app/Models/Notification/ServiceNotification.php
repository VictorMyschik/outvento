<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\ORM\ORM;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ServiceNotification extends ORM
{
    use AsSource;
    use Filterable;

    public const null UPDATED_AT = null;

    protected $table = 'service_notifications';

    public array $allowedSorts = [
        'user_id',
        'event',
        'channel',
        'communication_address', // For orchid display list only
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'event' => 'int',
        'created_at' => 'datetime',
    ];

    public function getChannel(): NotificationChannel
    {
        return NotificationChannel::from((string)$this->channel);
    }

    public function getEventType(): ServiceEvent
    {
        return ServiceEvent::from((int)$this->event);
    }

    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    public function getCommunication(): Communication
    {
        return Communication::loadByOrDie($this->communication_id);
    }
}
