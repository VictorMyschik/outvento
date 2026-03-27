<?php

declare(strict_types=1);

namespace App\Models\Travel;

use App\Models\ORM\ORM;
use App\Services\Travel\Enum\Activity;

class TravelActivity extends ORM
{
    public $timestamps = false;
    protected $table = 'travel_activities';

    public function getActivity(): Activity
    {
        return Activity::from($this->activity);
    }
}