<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\ORM\ORM;
use App\Models\User;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\NotificationType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class UserNotificationSetting extends ORM
{
    use AsSource;
    use Filterable;
    use ActiveFieldTrait;

    protected $table = 'user_notification_settings';

    public array $allowedSorts = [
        'user_id',
        'notification_key',
        'channel',
        'active',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'user_id'          => 'integer',
        'notification_key' => 'string',
        'channel'          => 'string',
        'active'           => 'boolean',
        'created_at'       => 'datetime',
    ];

    public function getType(): NotificationType
    {
        return NotificationType::from($this->notification_key);
    }

    public function getChannel(): NotificationChannel
    {
        return NotificationChannel::from($this->channel);
    }

    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }
}