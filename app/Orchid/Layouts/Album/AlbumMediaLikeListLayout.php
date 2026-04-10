<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Album;

use App\Models\Albums\AlbumMediaLike;
use App\Services\Albums\Enum\Icon;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AlbumMediaLikeListLayout extends Table
{
    public $target = 'likes-list';

    protected function columns(): iterable
    {
        return [
            TD::make('avatar', 'Avatar')->render(function (AlbumMediaLike $like) {
                return ViewField::make('')->view('admin.users.albums.user_avatar')->value(route('api.v1.user.avatar', ['user' => $like->user_id]));
            })->sort(),
            TD::make('user_name', 'User')->render(function (AlbumMediaLike $like) {
                return "<a href='" . route('profiles.details', ['user' => $like->user_id]) . "'>" . $like->user_name . "</a>";
            })->sort(),
            TD::make('icon', 'Icon')->render(function (AlbumMediaLike $like) {
                $icon = Icon::from($like->icon)->getCode();
                return ViewField::make('')->view('admin.raw')->value("<i class='fa-solid fa-$icon' style='color: red'></i>");
            })->sort(),
            TD::make('updated_at ', 'Created')->render(fn(AlbumMediaLike $like) => $like->updated_at->format('H:i:s d/m/Y'))->sort(),
            TD::make('', '#')->alignCenter()->render(function (AlbumMediaLike $like) {
                return Button::make('')->icon('trash')->method('deleteLike', [
                    'mediaId' => $like->media_id,
                    'userId'  => $like->user_id,
                ]);
            })->sort(),
        ];
    }

    protected function hoverable(): bool
    {
        return true;
    }
}