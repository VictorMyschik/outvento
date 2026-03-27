<?php

declare(strict_types=1);

namespace App\Models\Reference;

use App\Models\Lego\Fields\NameByLanguageFieldTrait;
use App\Models\ORM\ORM;
use App\Models\User;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class UserLocation extends ORM
{
    use AsSource;
    use Filterable;
    use NameByLanguageFieldTrait;

    protected $table = 'user_locations';

    protected $fillable = [
        'user_id',
        'city_id',
        'lat',
        'lng',
        'radius_km',
        'is_visible',
    ];

    protected array $allowedSorts = [
        'id',
        'user_id',
        'city_id',
        'lat',
        'lng',
        'radius_km',
        'is_visible',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getCity(): City
    {
        return City::loadByOrDie($this->city_id);
    }

    public function getUser(): User
    {
        return User::loadByOrDie($this->user_id);
    }
}
