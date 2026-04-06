<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Album;

use App\Models\Albums\Album;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AlbumListLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'id')->render(function (Album $album) {
                return Link::make((string)$album->id)->target('_blank')->stretched()->route('profiles.albums.details', ['user' => $album->user_id, 'album' => $album->id]);
            })->sort(),
            TD::make('title', 'Title')->sort(),
            TD::make('visibility', 'Visibility')->render(fn(Album $album) => $album->getVisibility()->getLabel())->sort(),
            TD::make('created_at', 'Created')->render(fn(Album $album) => $album->created_at->format('H:i:s d/m/Y'))->sort(),
            TD::make('updated_at', 'Updated')->render(fn(Album $album) => $album->updated_at?->format('H:i:s d/m/Y'))->sort(),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}
