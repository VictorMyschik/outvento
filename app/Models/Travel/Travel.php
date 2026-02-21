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
use App\Services\Travel\Enum\TravelVisible;
use App\Services\Travel\Enum\UserTravelRole;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        'visible',
        'members',
        'public_id',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getDateFrom(): Carbon
    {
        return Carbon::parse($this->date_from);
    }

    public function getDateTo(): Carbon
    {
        return Carbon::parse($this->date_to);
    }

    public function getDuration(): float
    {
        return $this->getDateFrom()->diffInDays($this->getDateTo());
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function getVisibleType(): TravelVisible
    {
        return TravelVisible::from($this->visible_type);
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

    public function getActivities(): Collection
    {
        return TravelActivity::where('travel_id', $this->id())->get();
    }

    public function getCountries(): Collection
    {
        return Country::join(TravelCountry::getTableName(), 'countries.id', '=', TravelCountry::getTableName() . '.country_id')
            ->where(TravelCountry::getTableName() . '.travel_id', $this->id())
            ->get(['countries.*']);
    }

    public function getPublicId(): ?string
    {
        return $this->public_id;
    }

    public function getDirNameForImages(): string
    {
        return self::STORAGE_PATH;
    }

    public function getMaxMembers(): ?int
    {
        return $this->members;
    }

    public function getMembers(): int
    {
        return $this->members_exists;
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

    public function getActivitiesForOrchid(): array
    {
        $out = [];

        /** @var TravelActivity $activity */
        foreach ($this->getActivities() as $activity) {
            $out[] = (int)$activity->activity;
        }

        return $out;
    }

    public function getCountriesForOrchid(): array
    {
        $out = [];

        /** @var Country $country */
        foreach ($this->getCountries() as $country) {
            $out[] = (int)$country->id;
        }

        return $out;
    }

    public function getOwnerId(): int
    {
        return UIT::where('travel_id', $this->id())->where('role', UserTravelRole::Owner->value)->value('user_id');
    }
}
