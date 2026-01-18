<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\ORM\ORM;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use App\Services\Notifications\Enum\EventType;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class UserNotificationSetting extends ORM
{
    use AsSource;
    use Filterable;
    use ActiveFieldTrait;

    protected $table = 'user_notification_settings';

    public array $allowedSorts = [
        'active',
        'user_id',
        'event_type',
        'communication_type', // For orchid list only
        'communication_address', // For orchid list only
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'event_type' => 'string',
        'active'     => 'boolean',
        'created_at' => 'datetime',
    ];

    public function getEventType(): EventType
    {
        return EventType::from($this->event_type);
    }

    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    public function getCommunication(): Communication
    {
        return Communication::loadByOrDie($this->communication_id);
    }

    public function getCommunicationType(): CommunicationType
    {
        return $this->getCommunication()->getType();
    }
}
