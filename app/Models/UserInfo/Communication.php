<?php

declare(strict_types=1);

namespace App\Models\UserInfo;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\NameByLanguageFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Communication extends ORM
{
    use AsSource;
    use Filterable;
    use NameByLanguageFieldTrait;
    use DescriptionNullableFieldTrait;

    use UserFieldTrait;

    protected $table = 'communications';
    protected $fillable = [
        'user_id',
        'type',// тип: телефон, email, факс...
        'address',
        'description',
    ];

    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $allowedSorts = [
        'user_id',
        'full_name',
        'address',
        'type',
        'email',
        'name',
        'description',
        'created_at',
        'updated_at',
    ];

    public function getType(): CommunicationType
    {
        return CommunicationType::loadByOrDie($this->type_id);
    }
}
