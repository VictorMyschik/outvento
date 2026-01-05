<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Models\ORM\ORM;
use App\Services\Newsletter\Enum\MediaType;
use App\Services\Newsletter\Enum\RelationMediaType;
use Illuminate\Support\Facades\Storage;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NewsMedia extends ORM
{
    use Filterable;
    use AsSource;

    public const null UPDATED_AT = null;

    protected $table = 'news_media';

    protected array $allowedSorts = [
        'id',
        'news_id',
        'type',
        'media_type',
        'path',
        'created_at',
    ];

    public function getType(): RelationMediaType
    {
        return RelationMediaType::from($this->type);
    }

    public function getMediaType(): MediaType
    {
        return MediaType::from($this->type);
    }

    public function getUrl(): string
    {
        return Storage::url($this->path);
    }
}
