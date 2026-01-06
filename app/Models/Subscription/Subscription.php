<?php

namespace App\Models\Subscription;

use App\Models\Lego\Fields\LanguageFieldTrait;
use App\Models\ORM\ORM;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Notifications\Enum\NotificationType;
use App\Services\Notifications\NotificationRecipientInterface;
use Illuminate\Notifications\Notifiable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Subscription extends ORM implements NotificationRecipientInterface
{
    use AsSource;
    use Filterable;
    use Notifiable;
    use LanguageFieldTrait;

    protected $table = 'subscriptions';

    protected array $allowedSorts = [
        'id',
        'email',
        'token',
        'type',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getToken(): string
    {
        return $this->token;
    }

    public function getType(): NotificationType
    {
        return NotificationType::from($this->type);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function routeNotificationForMail(): string
    {
        return $this->getEmail();
    }
}
