<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Lego\Fields\CreatedFieldTrait;
use App\Models\Lego\Fields\DeletedNullableFieldTrait;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\Lego\Fields\UpdatedNullableFieldTrait;
use App\Models\ORM\ORM;
use App\Models\Reference\Country;
use Illuminate\Support\Facades\Cache;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Travel extends ORM
{
    use AsSource;
    use Filterable;

    use TitleFieldTrait;
    use DescriptionNullableFieldTrait;
    use CreatedFieldTrait;
    use UpdatedNullableFieldTrait;
    use DeletedNullableFieldTrait;

    protected $table = 'travels';

    protected array $allowedSorts = [
        'id',
        'title',
        'description',
        'status',
        'user_id',
        'country_id',
        'public',
        'travel_type_id',
        'public_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'public_id',
        'country_id',
        'travel_type_id',
        'visible_kind',
        'public_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const VISIBLE_KIND_PUBLIC = 2; // публичный
    const VISIBLE_KIND_FOR_ME = 0; // только для меня, в публичном поиске не участвует
    const VISIBLE_KIND_PLATFORM = 1; // только для зарегистрированных пользователей

    public static function getVisibleKindList(): array
    {
        return [
            self::VISIBLE_KIND_PUBLIC   => __('mr-t.Public'),// 'Публичный',
            self::VISIBLE_KIND_FOR_ME   => __('mr-t.only_for_me'),// 'Только для меня',
            self::VISIBLE_KIND_PLATFORM => __('mr-t.Only for registered users'),// 'Только для зарегистрированных пользователей',
        ];
    }

    public static function getVisibleKindDescription(): array
    {
        return [
            self::VISIBLE_KIND_PUBLIC   => __('mr-t.Anyone can see this travel program'), // 'Любой пользователь может видеть эту походную программу',
            self::VISIBLE_KIND_FOR_ME   => __('mr-t.Only I can see this travel program'), // 'Только я могу видеть эту походную программу',
            self::VISIBLE_KIND_PLATFORM => __('mr-t.Only registered users can see this travel program'), // 'Только зарегистрированные пользователи могут видеть эту походную программу',
        ];
    }

    const STATUS_DRAFT = -1;
    const STATUS_ACTIVE = 1;
    const STATUS_ARCHIVED = 2;

    public static function getStatusList(): array
    {
        return [
            self::STATUS_DRAFT    => __('mr-t.Draft'),
            self::STATUS_ACTIVE   => __('mr-t.Active'),
            self::STATUS_ARCHIVED => __('mr-t.Archived'),
        ];
    }

    private const STORAGE_PATH = 'files/travel_images';

    #region ORM
    public function canView(?User $me = null): bool
    {
        if (self::VISIBLE_KIND_PUBLIC === $this->getVisibleKind()) {
            return true;
        }

        if ($me) {
            if ($me->id() === $this->user_id || self::VISIBLE_KIND_PLATFORM === $this->getVisibleKind()) {
                return true;
            }
        }

        return false;
    }

    public function canEdit(?User $me = null): bool
    {
        // Authorised user only
        if (!$me) {
            return false;
        }

        if (!$this->canView($me)) {
            return false;
        }

        if ($me->id() !== $this->user_id) {
            return false;
        }

        return true;
    }

    public function canDelete(User $user): bool
    {
        if (!$this->canEdit($user)) {
            return false;
        }

        return true;
    }

    public function afterSave(): void
    {
        if (!$this->getPublicId()) {
            $this->setPublicId(crc32((string)$this->id()));
            $this->save_mr();
        }
    }

    public function flush(): void
    {
        Cache::forget('travel_image_full_list_' . $this->id());
    }

    public function beforeDelete(): void
    {
        foreach ($this->getImagesList() as $image) {
            $image->delete();
        }

        $this->getMainImage()?->delete();
    }

    #endregion
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusName(): string
    {
        return self::getStatusList()[$this->getStatus()];
    }

    public function setStatus(int $value): void
    {
        $this->status = $value;
    }

    public function getVisibleKind(): int
    {
        return $this->visible_kind;
    }

    public function getVisibleKindName(): string
    {
        return self::getVisibleKindList()[$this->getVisibleKind()];
    }

    public function setVisibleKind(int $value): void
    {
        if (!array_key_exists($value, self::getVisibleKindList())) {
            throw new \InvalidArgumentException('Invalid visible kind');
        }

        $this->visible_kind = $value;
    }

    public function getUser(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function setUserID(int $value): void
    {
        $this->user_id = $value;
    }

    public function getCountry(): Country
    {
        return Country::loadByOrDie($this->country_id);
    }

    public function setCountryID(int $value): void
    {
        $this->country_id = $value;
    }

    public function getTravelType(): TravelType
    {
        return TravelType::loadByOrDie($this->travel_type_id);
    }

    public function setTravelTypeID(int $value): void
    {
        $this->travel_type_id = $value;
    }

    public function getPublicId(): ?string
    {
        return $this->public_id;
    }

    public function setPublicId(?int $value): void
    {
        $this->public_id = $value;
    }

    public function getDirNameForImages(): string
    {
        return self::STORAGE_PATH;
    }

    public function getFullImagesList(): array
    {
        return TravelImage::where('travel_id', $this->id())->get()->all();
    }

    public function getMainImage(): ?TravelImage
    {
        return TravelImage::where('travel_id', $this->id())->where('kind', TravelImage::KIND_LOGO)->value('name');
    }

    /**
     * @return TravelImage[]
     */
    public function getImagesList(): array
    {
        return Cache::rememberForever('travel_image_list_' . $this->id(), function () {
            return TravelImage::where('travel_id', $this->id())->where('kind', TravelImage::KIND_LIST)->get()->all();
        });
    }
}
