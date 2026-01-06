<?php

namespace App\Orchid\Layouts\References;

use App\Models\Reference\Country;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CityEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('city.country_id')
                ->title('Страна')
                ->required()->empty('Select country')
                ->fromModel(Country::class, 'name_ru'),
            Input::make('city.name_ru')
                ->title('RU')
                ->required()
                ->maxlength(255),
            Input::make('city.name_en')
                ->title('EN')
                ->required()
                ->maxlength(255),
            Input::make('city.name_pl')
                ->title('PL')
                ->required()
                ->maxlength(255),
        ];
    }
}
