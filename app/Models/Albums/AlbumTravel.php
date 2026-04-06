<?php

declare(strict_types=1);

namespace App\Models\Albums;

use App\Models\ORM\ORM;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class AlbumTravel extends ORM
{
    use AsSource;
    use Filterable;

    public const string TABLE = 'album_travels';
    public const null UPDATED_AT = null;

    protected $table = self::TABLE;

    public $fillable = [
        'album_id',
        'travel_id',
    ];

    public $casts = [
        'created_at',
    ];
}
