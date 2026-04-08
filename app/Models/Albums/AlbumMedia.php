<?php

declare(strict_types=1);

namespace App\Models\Albums;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class AlbumMedia extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'album_media';

    protected $table = self::TABLE;

    protected array $allowedSorts = [
        'id',
        'file_type',
        'mime',
        'size',
        'path',
        'sort',
        'hash',
        'description',
        'created_at',
        'updated_at',
    ];
}