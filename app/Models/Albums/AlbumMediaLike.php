<?php

declare(strict_types=1);

namespace App\Models\Albums;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class AlbumMediaLike extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'album_media_likes';

    protected $table = self::TABLE;

    protected $fillable = [
        'media_id',
        'user_id',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];
}

