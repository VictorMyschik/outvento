<?php

namespace App\Models;

use App\Models\Notification\UserNotificationSetting;
use App\Services\Notifications\Enum\NotificationType;
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
        'telegram_chat_id',
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
    ];


    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'telegram_chat_id',
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
            ->where('notification_key', $key)
            ->where('enabled', true)
            ->pluck('channel')
            ->toArray();
    }

    public function routeNotificationForTelegram(): ?string
    {
        return $this->telegram_chat_id;
    }

    public function getUnsubscribeToken(NotificationType $type): string
    {
        return UserNotificationSetting::where('user_id', $this->id)
            ->where('notification_key', $type->value)
            ->value('token');
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
            'deleted_at'       => now(),
            'name'             => 'deleted',
            'email'            => 'deleted',
            'password'         => Hash::make(str()->random(32)),
            'first_name'       => null,
            'last_name'        => null,
            'birthday'         => null,
            'telegram_chat_id' => null,
            'about'            => null,
        ]);

        $this->save();
    }
}
