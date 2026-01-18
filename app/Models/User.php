<?php

namespace App\Models;

use App\Models\Notification\UserNotificationSetting;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use App\Services\Notifications\NotificationChannelMapper;
use App\Services\Notifications\NotificationRecipientInterface;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\Gender;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Orchid\Filters\Filterable;
use Orchid\Platform\Models\User as Authenticatable;
use Orchid\Screen\AsSource;

class User extends Authenticatable implements MustVerifyEmail, NotificationRecipientInterface
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

    public function notificationChannelsFor(string $notificationClass): array
    {
        $key = $notificationClass::KEY;

        return $this->notificationSettings()
            ->join(Communication::getTableName(), UserNotificationSetting::getTableName() . '.communication_id', '=', Communication::getTableName() . '.id')
            ->join(CommunicationType::getTableName(), CommunicationType::getTableName() . '.id', '=', Communication::getTableName() . '.type_id')
            ->where('event_type', $key)
            ->where(UserNotificationSetting::getTableName() . '.active', true)
            ->pluck(CommunicationType::getTableName() . '.code')
            ->map(fn ($code) => NotificationChannelMapper::map($code))
            ->unique()
            ->values()
            ->toArray();
    }

    public function routeNotificationForTelegram(): ?string
    {
        return $this->telegram_chat_id;
    }

    public function getUnsubscribeToken(): string
    {
        return $this->subscription_token;
    }

    public function routeNotificationForMail($notification = null): string
    {
        return $this->email;
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
