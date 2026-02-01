<?php

declare(strict_types=1);

namespace App\Models\Subscription;

use App\Models\Lego\Fields\LanguageFieldTrait;
use App\Models\ORM\ORM;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\System\Enum\Language;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Subscription extends ORM implements NotificationRecipientInterface, HasLocalePreference
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

    public function getType(): EventType
    {
        return EventType::from($this->type);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUnsubscribeToken(): string
    {
        return $this->token;
    }

    public function preferredLocale(): string
    {
        return Language::from($this->language)->getCode() ?? config('app.locale');
    }

    public function notificationChannelsFor(string $notificationClass): array
    {
        return [NotificationChannel::Email->value];
    }

    public function routeNotificationForMail($notification = null): string
    {
        return $this->getEmail();
    }

    public function routeNotificationForTelegram(Notification $notification): string|int|null
    {
        return null;
    }
}
