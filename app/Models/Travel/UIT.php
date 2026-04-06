<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\Lego\Fields\TravelFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use App\Services\Travel\Enum\UITStatus;
use App\Services\Travel\Enum\UserTravelRole;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class UIT extends ORM
{
    use AsSource;
    use Filterable;

    use UserFieldTrait;
    use TravelFieldTrait;

    public const string TABLE = 'uit';

    protected $table = self::TABLE;

    protected array $allowedSorts = [
        'travel_id',
        'user_id',
        'status',
        'role',
    ];

    public function getRole(): UserTravelRole
    {
        return UserTravelRole::from($this->role);
    }

    public function getStatus(): UITStatus
    {
        return UITStatus::from($this->status);
    }

    public function setStatus(UITStatus $value): void
    {
        $this->status = $value->value;
    }
}
