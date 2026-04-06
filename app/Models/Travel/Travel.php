<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\Lego\Fields\DeletedNullableFieldTrait;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\LanguageFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;
use App\Models\Reference\City;
use App\Models\Reference\Country;
use App\Models\User;
use App\Services\System\Enum\Language;
use App\Services\Travel\Enum\TravelPointType;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisible;
use App\Services\Travel\Enum\UserTravelRole;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Travel extends ORM
{
    use AsSource;
    use Filterable;

    use LanguageFieldTrait;
    use TitleFieldTrait;
    use DescriptionNullableFieldTrait;
    use DeletedNullableFieldTrait;

    public const string TABLE = 'travels';

    protected $table = self::TABLE;

    protected array $allowedSorts = [
        'id',
        'title',
        'date_from',
        'date_to',
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
        'date_from'  => 'datetime',
        'date_to'    => 'datetime',
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

    public function getOwner(): User
    {
        return UIT::where('travel_id', $this->id())->where('role', UserTravelRole::Owner->value)->first()->getUser();
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

    public function getStartCity(): ?City
    {
        return TravelPoint::where('travel_id', $this->id())
            ->where('type', TravelPointType::Start->value)
            ->first()?->getCity();
    }

    public function getPublicId(): ?string
    {
        return $this->public_id;
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
        return TravelMedia::where('travel_id', $this->id())->get()->all();
    }

    public function getExistsLogo(): ?string
    {
        if ($id = TravelMedia::where('travel_id', $this->id())->where('is_avatar', true)->value('id')) {
            return route('api.v1.travel.image', [
                'travel' => $this->id,
                'media'  => $id,
            ]);
        }

        return null;
    }

    public function getAvatarExt(): string
    {
        return $this->getExistsLogo() ?: '/images/travel_logo_circle.webp';
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

    public function getActivitiesByLanguage(Language $language): array
    {
        $out = [];

        /** @var TravelActivity $activity */
        foreach ($this->getActivities() as $activity) {
            $out[] = (string)$activity->getActivity()->getLabel($language);
        }

        return $out;
    }

    public function getCountriesByLanguage(Language $language): array
    {
        $out = [];

        /** @var Country $country */
        foreach ($this->getCountries() as $country) {
            $out[] = $country->getName($language);
        }

        return $out;
    }
}
