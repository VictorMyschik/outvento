<?php

declare(strict_types=1);

namespace App\Models\UserInfo;

use App\Models\Lego\Fields\DescriptionNullableFieldTrait;
use App\Models\Lego\Fields\UserFieldTrait;
use App\Models\ORM\ORM;
use App\Services\User\Enum\CommunicateType;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Communicate extends ORM
{
    use AsSource;
    use Filterable;
    use DescriptionNullableFieldTrait;

    use UserFieldTrait;

    protected $table = 'communicates';
    protected $fillable = [
        'user_id',
        'type',// тип: телефон, email, факс...
        'address',
        'description'
    ];

    public function getType(): CommunicateType
    {
        return CommunicateType::from($this->type);
    }
}
