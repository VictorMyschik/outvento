<?php

namespace App\Models\System;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\ORM\ORM;
use App\Services\System\Enum\CronKeyEnum;
use Carbon\Carbon;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Cron extends ORM
{
    use AsSource;
    use Filterable;
    use DescriptionNullableFieldTrait;
    use ActiveFieldTrait;

    protected $table = 'cron';

    protected array $allowedSorts = [
        'id',
        'active',
        'period',
        'name',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'last_work'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function setPeriod(int $value): void
    {
        $this->period = $value;
    }

    public function getCronKey(): CronKeyEnum
    {
        return CronKeyEnum::from($this->cron_key);
    }

    public function setCronKey(string $value): void
    {
        $this->cron_key = $value;
    }

    public function getLastWork(): ?Carbon
    {
        return $this->last_work;
    }

    public function setLastWork(Carbon $value): void
    {
        $this->last_work = $value;
    }
}
