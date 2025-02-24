<?php

namespace App\Models;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\CreatedFieldTrait;
use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameFieldTrait;
use App\Models\Lego\Fields\UpdatedNullableFieldTrait;
use App\Models\ORM\ORM;
use Carbon\Carbon;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Cron extends ORM
{
    use AsSource;
    use Filterable;
    use NameFieldTrait;
    use DescriptionNullableFieldTrait;
    use ActiveFieldTrait;
    use CreatedFieldTrait;
    use UpdatedNullableFieldTrait;

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

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function setPeriod(int $value): void
    {
        $this->period = $value;
    }

    public function getCronKey(): string
    {
        return $this->cron_key;
    }

    public function setCronKey(string $value): void
    {
        $this->cron_key = $value;
    }

    public function getLastWork(): ?string
    {
        return $this->last_work;
    }

    public function setLastWork(Carbon $value): void
    {
        $this->last_work = $value;
    }

    public function getLastWorkObject(): ?Carbon
    {
        return $this->last_work ? new Carbon($this->last_work) : null;
    }
}
