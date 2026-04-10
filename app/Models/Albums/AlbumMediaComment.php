<?php

declare(strict_types=1);

namespace App\Models\Albums;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class AlbumMediaComment extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'album_media_comments';

    protected $table = self::TABLE;

    protected $fillable = [
        'media_id',
        'user_id',
        'body',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

