<?php

namespace App\Models;

use App\Models\Lego\Fields\CreatedFieldTrait;
use App\Models\Lego\Fields\TravelFieldTrait;
use App\Models\Lego\Fields\UpdatedNullableFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class UIH extends ORM
{
    use AsSource;
    use Filterable;

    use UserFieldTrait;
    use TravelFieldTrait;
    use CreatedFieldTrait;
    use UpdatedNullableFieldTrait;

    protected $table = 'uih';

    protected array $allowedSorts = [
        'travel_id',
        'user_id',
        'status',
    ];

    protected $fillable = [
        'travel_id',
        'user_id',
        'status',
    ];

    const STATUS_NEW = 0; // Новый участник
    const STATUS_APPROVED = 1; // Подтверждённый всеми сторонами
    const STATUS_REJECTED = 2; // Отклонённый всеми сторонами

    public static function getStatusList(): array
    {
        return [
            self::STATUS_NEW      => 'Новый',
            self::STATUS_APPROVED => 'Подтверждён',
            self::STATUS_REJECTED => 'Отклонён',
        ];
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusName(): string
    {
        return self::getStatusList()[$this->status];
    }

    public function setStatus(int $value): void
    {
        $this->status = $value;
    }
}
