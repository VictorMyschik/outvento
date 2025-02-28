<?php

namespace App\Orchid\Layouts\References;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TravelTypeEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('travel-type.name_ru')
                ->title('RU')
                ->required()
                ->maxlength(255),
            Input::make('travel-type.name_en')
                ->title('EN')
                ->required()
                ->maxlength(255),
            Input::make('travel-type.name_pl')
                ->title('PL')
                ->required()
                ->maxlength(255),
        ];
    }
}
