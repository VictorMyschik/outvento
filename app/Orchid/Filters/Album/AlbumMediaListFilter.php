<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Album;

use App\Models\Albums\AlbumMedia;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;

class AlbumMediaListFilter extends Filter
{
    public static function runQuery(int $albumId): Builder
    {
        return AlbumMedia::filters([self::class])->where('album_id', $albumId);
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }
}