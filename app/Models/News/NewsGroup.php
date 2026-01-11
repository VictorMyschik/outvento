<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Models\Lego\Fields\ActiveFieldTrait;
use App\Models\Lego\Fields\CodeFieldTrait;
use App\Models\Lego\Fields\TitleFieldTrait;
use App\Models\ORM\ORM;
use App\Services\System\Enum\Language;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NewsGroup extends ORM
{
    use AsSource;
    use Filterable;
    use CodeFieldTrait;
    use TitleFieldTrait;
    use ActiveFieldTrait;

    protected $table = 'news_groups';

    protected array $allowedSorts = [
        'id',
        'active',
        'title',
        'language',
        'code',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getLanguage(): Language
    {
        return Language::from($this->language);
    }
}
