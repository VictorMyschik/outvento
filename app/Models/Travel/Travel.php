<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\Lego\Fields\DeletedNullableFieldTrait;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;
use App\Models\Reference\Country;
use App\Models\User;
use App\Services\Travel\Enum\ImageType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisibleType;
use Illuminate\Support\Facades\Cache;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Travel extends ORM
{
    use AsSource;
    use Filterable;

    use TitleFieldTrait;
    use DescriptionNullableFieldTrait;
    use DeletedNullableFieldTrait;

    protected $table = 'travels';

    protected array $allowedSorts = [
        'id',
        'title',
        'preview',
        'status',
        'user_id',
        'country_id',
        'public',
        'travel_type_id',
        'public_id',
        'visible_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function getVisibleType(): TravelVisibleType
    {
        return TravelVisibleType::from($this->visible_type);
    }

    private const string STORAGE_PATH = 'files/travel_images';

    public function getStatus(): TravelStatus
    {
        return TravelStatus::from($this->status);
    }

    public function getUser(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function getCountry(): Country
    {
        return Country::loadByOrDie($this->country_id);
    }

    public function getTravelType(): TravelType
    {
        return TravelType::loadByOrDie($this->travel_type_id);
    }

    public function getPublicId(): ?string
    {
        return $this->public_id;
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
        return TravelImage::where('travel_id', $this->id())->where('kind', ImageType::LOGO)->value('name');
    }

    /**
     * @return TravelImage[]
     */
    public function getImagesList(): array
    {
        return Cache::rememberForever('travel_image_list_' . $this->id(), function () {
            return TravelImage::where('travel_id', $this->id())->where('type', ImageType::PHOTO)->get()->all();
        });
    }
}
