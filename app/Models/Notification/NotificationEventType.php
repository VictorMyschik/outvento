<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\ReferenceImageFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;
use App\Models\Reference\ReferenceBaseInterface;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NotificationEventType extends ORM implements ReferenceBaseInterface
{
    use AsSource;
    use Filterable;
    use TitleFieldTrait;
    use ReferenceImageFieldTrait;
    use DescriptionNullableFieldTrait;

    protected $table = 'notification_event_types';

    public array $allowedSorts = [
        'id',
        'category',
        'code',
        'title',
        'description',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}