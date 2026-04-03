<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Notification\NotificationMute;
use App\Models\Notification\ServiceNotification;
use App\Models\Reference\UserLocation;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\SocialAccount;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\Gender;
use App\Services\User\Enum\RelationshipStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Orchid\Filters\Filterable;
use Orchid\Platform\Models\User as Authenticatable;
use Orchid\Screen\AsSource;

class User extends Authenticatable implements MustVerifyEmail, NotificationRecipientInterface, HasLocalePreference
{
    use AsSource;
    use Filterable;
    use Notifiable, HasApiTokens;

    public const string TYPE_VIEW = 'view';

    public const string TYPE_EDIT = 'edit';

    public const string TYPE_DELETE = 'delete';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'language',
        'first_name',
        'last_name',
        'birthday',
        'deleted_at',
        'subscription_token',
        'email_verified_at',
        'relationship_status',
        'avatar',
        'location',
        'gender',
        'about',
        'visibility',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    protected $casts = [
        'permissions'       => 'array',
        'email_verified_at' => 'datetime',
        'birthday'          => 'date',
        'deleted_at'        => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];


    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'subscription_token',
        'first_name',
        'last_name',
        'language',
        'gender',
        'birthday',
        'about',
        'permissions',
        'created_at',
        'updated_at',
    ];

    public function getRolesDisplay(): string
    {
        $roles = $this->getRoles()->pluck('name')->all();

        return 'Roles: ' . implode(', ', $roles);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function preferredLocale(): string
    {
        return Language::from($this->language)->getCode() ?? config('app.locale');
    }

    public static function getTableName(): string
    {
        return 'users';
    }

    public static function canView(string $objectCheckName): bool
    {
        $user = Auth::user();

        return $user->hasAccess($objectCheckName . '.' . self::TYPE_VIEW);
    }

    public static function canEdit(string $objectCheckName): bool
    {
        if (!self::canView($objectCheckName)) {
            return false;
        }

        $user = Auth::user();

        return $user->hasAccess($objectCheckName . '.' . self::TYPE_EDIT);
    }

    public static function canDelete(string $objectCheckName): bool
    {
        if (!self::canEdit($objectCheckName)) {
            return false;
        }

        $user = Auth::user();

        return $user->hasAccess($objectCheckName . '.' . self::TYPE_DELETE);
    }

    public function getRelationshipStatus(): RelationshipStatus
    {
        return RelationshipStatus::from((int)$this->relationship_status);
    }

    public function isSuperAdmin(): bool
    {
        return !is_null($this->permissions);
    }

    public function getAvatar(): ?string
    {
        return $this->avatar ? route('api.v1.user.avatar', ['user' => $this->id]) : null;
    }

    public function getAvatarExt(): string
    {
        return $this->getAvatar() ?: '/images/avatar.png';
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function notificationSettings(): HasMany
    {
        return $this->hasMany(ServiceNotification::class);
    }

    /**
     * Суть метода - вернуть каналы уведомлений ['email', 'telegram'], которые пользователь
     * выбрал для получения конкретного типа уведомлений
     */
    public function notificationChannelsFor(string $notificationClass): array
    {
        //return [NotificationChannel::Email->value, NotificationChannel::Telegram->value, NotificationChannel::Internal->value]; // - Будет ошибка если notification не реализовал один из каналов

        // Вернёт список каналов, которые есть и используются
        return $this->notificationSettings()
            ->leftJoin(NotificationMute::getTableName(), function ($join) use ($notificationClass) {
                $join->on(ServiceNotification::getTableName() . '.user_id', '=', NotificationMute::getTableName() . '.user_id')
                    ->where(NotificationMute::getTableName() . '.event', $notificationClass::KEY);
            })
            ->join(Communication::getTableName(), ServiceNotification::getTableName() . '.communication_id', '=', Communication::getTableName() . '.id')
            ->where(ServiceNotification::getTableName() . '.event', $notificationClass::KEY)
            ->where(NotificationMute::getTableName() . '.user_id', null) // Not muted
            ->pluck(ServiceNotification::getTableName() . '.channel')
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getCommunicationAddressFor(string $channel, int $event): ?string
    {
        if ($channel === NotificationChannel::Internal->value) {
            if (NotificationMute::where('user_id', $this->id)->where('event', $event)->exists()) {
                return null;
            }

            return (string)$this->id;
        }

        $value = match ($channel) {
            NotificationChannel::Email->value => Communication::getTableName() . '.address',
            NotificationChannel::Telegram->value => Communication::getTableName() . '.address_ext',
            default => null,
        };

        return $this->notificationSettings()
            ->leftJoin(NotificationMute::getTableName(), function ($join) use ($event) {
                $join->on(ServiceNotification::getTableName() . '.user_id', '=', NotificationMute::getTableName() . '.user_id')
                    ->where(NotificationMute::getTableName() . '.event', $event);
            })
            ->join(
                Communication::getTableName(),
                ServiceNotification::getTableName() . '.communication_id',
                '=',
                Communication::getTableName() . '.id'
            )
            ->where(ServiceNotification::getTableName() . '.event', $event)
            ->where(NotificationMute::getTableName() . '.user_id', null) // Not muted
            ->where(ServiceNotification::getTableName() . '.channel', $channel)
            ->value($value);
    }

    public function routeNotificationForMail(Notification $notification): string|array|null
    {
        $email = null;

        if (isset(ServiceEvent::getSelectList()[$notification::KEY])) {
            $email = $this->getCommunicationAddressFor(
                channel: NotificationChannel::Email->value,
                event: $notification::KEY
            );
        }

        return $email ?: $this->email;
    }

    public function routeNotificationForTelegram(Notification $notification): string|int|null
    {
        if (isset(ServiceEvent::getSelectList()[$notification::KEY])) {
            return $this->getCommunicationAddressFor(
                channel: NotificationChannel::Telegram->value,
                event: $notification::KEY
            );
        }

        return null;
    }

    public function routeNotificationForInternal(Notification $notification): int|string|null
    {
        if (isset(ServiceEvent::getSelectList()[$notification::KEY])) {
            return $this->getCommunicationAddressFor(
                channel: NotificationChannel::Internal->value,
                event: $notification::KEY
            );
        }

        return null;
    }

    public function getUnsubscribeToken(): string
    {
        return $this->subscription_token;
    }

    public function getLanguage(): Language
    {
        return Language::from($this->language);
    }

    public function getUserLocation(): ?UserLocation
    {
        return UserLocation::where('user_id', $this->id)->first();
    }

    public function getGender(): ?Gender
    {
        return $this->gender ? Gender::from($this->gender) : null;
    }

    public function softDelete(): void
    {
        $this->fill([
            'deleted_at'         => now(),
            'name'               => 'deleted',
            'email'              => (int)now()->timestamp . '@deleted.com',
            'password'           => Hash::make(str()->random(32)),
            'first_name'         => null,
            'last_name'          => null,
            'birthday'           => null,
            'subscription_token' => null,
            'about'              => null,
            'gender'             => null,
            'visibility'         => 0,
        ]);

        $this->save();
    }
}
