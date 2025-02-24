<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    public const TYPE_VIEW = 'view';

    public const TYPE_EDIT = 'edit';

    public const TYPE_DELETE = 'delete';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
           'id'         => Where::class,
           'name'       => Like::class,
           'email'      => Like::class,
           'updated_at' => WhereDateStartEnd::class,
           'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
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
}
