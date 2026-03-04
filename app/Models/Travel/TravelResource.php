<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;
use App\Services\Travel\Enum\TravelResourceType;

class TravelResource extends ORM
{
    protected $table = 'travel_resources';

    public $casts = [
        'sort'       => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getType(): TravelResourceType
    {
        return TravelResourceType::from($this->type);
    }
}