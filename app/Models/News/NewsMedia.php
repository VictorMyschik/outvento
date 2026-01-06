<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Models\ORM\ORM;
use App\Services\Newsletter\ImageUploader\Enum\NewsFileType;
use App\Services\Newsletter\ImageUploader\Enum\NewsMediaType;
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
        'file_type',
        'media_type',
        'path',
        'created_at',
    ];

    public $casts = [
        'news_id'    => 'integer',
        'file_type'  => 'integer',
        'media_type' => 'integer',
        'path'       => 'string',
        'created_at' => 'datetime',
    ];

    public function getType(): NewsFileType
    {
        return NewsFileType::from($this->file_type);
    }

    public function getMediaType(): NewsMediaType
    {
        return NewsMediaType::from($this->media_type);
    }

    public function getUrl(): string
    {
        return Storage::url($this->path);
    }
}
