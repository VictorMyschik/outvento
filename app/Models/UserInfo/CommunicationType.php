<?php

declare(strict_types=1);

namespace App\Models\UserInfo;

use App\Models\Lego\Fields\NameByLanguageFieldTrait;
use App\Models\Lego\Fields\ReferenceImageFieldTrait;
use App\Models\ORM\ORM;
use Illuminate\Support\Facades\Storage;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class CommunicationType extends ORM
{
    use AsSource;
    use Filterable;
    use NameByLanguageFieldTrait;
    use ReferenceImageFieldTrait;

    public $timestamps = false;

    protected $table = 'communication_types';

    protected array $allowedSorts = [
        'name_ru',
        'name_en',
        'name_pl',
    ];

    public function afterDelete(): void
    {
        $this->getImagePath() && Storage::delete($this->getImagePath());
    }
}
