<?php

declare(strict_types=1);

namespace App\Models\Albums;

use App\Models\ORM\ORM;

class AlbumMedia extends ORM
{
    public const string TABLE = 'album_media';

    protected $table = self::TABLE;
}