<?php

namespace App\Models;

use App\Models\Lego\Fields\CreatedFieldTrait;
use App\Models\Lego\Fields\UpdatedNullableFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use Carbon\Carbon;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class UserInfo extends ORM
{
    use AsSource;
    use Filterable;

    use UserFieldTrait;
    use CreatedFieldTrait;
    use UpdatedNullableFieldTrait;

    protected $table = 'user_info';

    protected array $allowedSorts = [
        'full_name',
        'gender',
        'birthday',
    ];

    protected $fillable = [
        'full_name',
        'gender',
        'birthday',
        'about',
    ];

    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

    public static function getGenderList(): array
    {
        return [
            self::GENDER_MALE   => 'Мужской',
            self::GENDER_FEMALE => 'Женский',
        ];
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function setFullName(string $value): void
    {
        $this->full_name = $value;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function getGenderName(): string
    {
        return self::getGenderList()[$this->getGender()];
    }

    public function setGender(int $value): void
    {
        $this->gender = $value;
    }

    public function getBirthday(): ?string
    {
        return $this->birthday;
    }

    public function getBirthdayObject(): ?Carbon
    {
        return $this->birthday ? Carbon::parse($this->birthday) : null;
    }

    public function setBirthday(?string $value): void
    {
        $this->birthday = $value;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $value): void
    {
        $this->about = $value;
    }
}
