<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use App\Models\Reference\Country;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CountryEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('country.continent')->title('Континент')->options(Country::CONTINENTS)->required(),
            Input::make('country.name_ru')
                ->title('RU')
                ->required()
                ->maxlength(255),
            Input::make('country.name_en')
                ->title('EN')
                ->required()
                ->maxlength(255),
            Input::make('country.name_pl')
                ->title('PL')
                ->required()
                ->maxlength(255),
            Input::make('country.iso3166alpha2')
                ->title('ISO 3166-1 alpha-2')
                ->required()
                ->maxlength(2),
            Input::make('country.iso3166alpha3')
                ->title('ISO 3166-1 alpha-3')
                ->required()
                ->maxlength(3),
            Input::make('country.iso3166numeric')
                ->title('ISO 3166-1 numeric')
                ->required()
                ->maxlength(3),
        ];
    }
}
