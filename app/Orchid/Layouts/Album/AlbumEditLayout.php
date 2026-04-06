<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Album;

use App\Services\Albums\Enum\Visibility;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class AlbumEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('album.title')
                ->title('Title')
                ->required()
                ->type('text')
                ->max(255),
            Select::make('album.visibility')
                ->required()
                ->options(Visibility::getSelectList())
                ->title('Visibility'),
            TextArea::make('album.description')
                ->title('Description')
                ->rows(5)
        ];
    }
}