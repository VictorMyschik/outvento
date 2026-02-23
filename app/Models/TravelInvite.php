<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Lego\Fields\TravelFieldTrait;
use App\Models\Lego\Fields\UserNullableFieldTrait;
use App\Models\ORM\ORM;
use App\Models\Travel\Travel;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TravelInvite extends ORM
{
    use AsSource;
    use Filterable;

    use UserNullableFieldTrait;
    use TravelFieldTrait;

    protected $table = 'travel_invites';

    protected $fillable = [
        'travel_id',
        'user_id',
        'email',
        'status',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getTravel(): Travel
    {
        return Travel::loadByOrDie((int)$this->travel_id);
    }
}
