<?php

namespace App\Orchid\Layouts\References;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TravelTypeEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('travel-type.name')
                ->title('Name')
                ->required()
                ->maxlength(255),

            TextArea::make('travel-type.description')
                ->title('Description')
                ->rows(5)
                ->maxlength(8000),
        ];
    }
}
