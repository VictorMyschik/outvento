<?php

namespace App\Models;

use App\Models\Notification\UserNotificationSetting;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use App\Models\UserInfo\SocialAccount;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\Gender;
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
        'about',
        'email_verified_at',
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

    public function isSuperAdmin(): bool
    {
        return !is_null($this->permissions);
    }

    public function getAvatar(): ?string
    {
        return $this->avatar ? asset('storage' . $this->avatar) : null;
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function notificationSettings(): HasMany
    {
        return $this->hasMany(UserNotificationSetting::class);
    }

    /**
     * Суть метода - вернуть каналы уведомлений ['email', 'telegram'], которые пользователь
     * выбрал для получения конкретного типа уведомлений
     */
    public function notificationChannelsFor(string $notificationClass): array
    {
        return [NotificationChannel::Email->value, NotificationChannel::Telegram->value];

        // Вернёт список каналов, которые есть в используются
        /*$key = $notificationClass::KEY;

        return $this->notificationSettings()
            ->join(Communication::getTableName(), UserNotificationSetting::getTableName() . '.communication_id', '=', Communication::getTableName() . '.id')
            ->join(CommunicationType::getTableName(), CommunicationType::getTableName() . '.id', '=', Communication::getTableName() . '.type_id')
            ->where('event_type', $notificationClass::KEY)
            ->where(UserNotificationSetting::getTableName() . '.active', true)
            ->pluck(CommunicationType::getTableName() . '.code')
            ->map(fn($code) => NotificationChannelMapper::map($code))
            ->unique()
            ->values()
            ->toArray();*/
    }

    protected function getCommunicationAddressFor(string $channel, string $eventKey): ?string
    {
        return $this->notificationSettings()
            ->join(
                Communication::getTableName(),
                UserNotificationSetting::getTableName() . '.communication_id',
                '=',
                Communication::getTableName() . '.id'
            )
            ->join(
                CommunicationType::getTableName(),
                CommunicationType::getTableName() . '.id',
                '=',
                Communication::getTableName() . '.type_id'
            )
            ->where(UserNotificationSetting::getTableName() . '.event_type', $eventKey)
            ->where(UserNotificationSetting::getTableName() . '.active', true)
            ->where(CommunicationType::getTableName() . '.code', $channel)
            ->value(Communication::getTableName() . '.address');
    }

    public function routeNotificationForMail(Notification $notification): string|array|null
    {
        if (isset(EventType::getSelectList()[$notification::KEY])) {
            return $this->getCommunicationAddressFor(
                channel: NotificationChannel::Email->value,
                eventKey: $notification::KEY
            );
        }

        return $this->email;
    }

    public function routeNotificationForTelegram(Notification $notification): string|int|null
    {
        if (isset(EventType::getSelectList()[$notification::KEY])) {
            return $this->getCommunicationAddressFor(
                channel: NotificationChannel::Telegram->value,
                eventKey: $notification::KEY
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
        ]);

        $this->save();
    }
}
