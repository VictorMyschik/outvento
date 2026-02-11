<?php

declare(strict_types=1);

namespace App\Models\UserInfo;

use App\Models\Lego\Fields\NameByLanguageFieldTrait;
use App\Models\Lego\Fields\ReferenceImageFieldTrait;
use App\Models\ORM\ORM;
use App\Models\Reference\ReferenceBaseInterface;
use App\Services\User\Enum\CommunicationTypeCode;
use Illuminate\Support\Facades\Storage;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class CommunicationType extends ORM implements ReferenceBaseInterface
{
    use AsSource;
    use Filterable;
    use NameByLanguageFieldTrait;
    use ReferenceImageFieldTrait;

    public $timestamps = false;

    protected $table = 'communication_types';

    protected array $allowedSorts = [
        'code',
        'title',
    ];

    public function afterDelete(): void
    {
        $this->getImagePath() && Storage::delete($this->getImagePath());
    }

    public function getCode(): CommunicationTypeCode
    {
        return CommunicationTypeCode::from($this->code);
    }
}
