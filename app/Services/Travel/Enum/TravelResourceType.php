<?php

declare(strict_types=1);

namespace App\Services\Travel\Enum;

enum TravelResourceType: int
{
    case File = 1;
    case Link = 2;
}
