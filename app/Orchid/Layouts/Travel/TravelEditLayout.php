<?php

namespace App\Orchid\Layouts\Travel;

use App\Models\Travel;
use App\Models\TravelType;
use App\Models\Reference\Country;
use App\Models\User;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TravelEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('travel.name')
                ->title('Name')
                ->required()
                ->maxlength(255),

            TextArea::make('travel.description')
                ->title('Description')
                ->rows(5)
                ->maxlength(8000),

            Select::make('travel.status')
                ->title('Status')
                ->required()
                ->options(Travel::getStatusList()),

            Select::make('travel.user_id')
                ->title('User')
                ->required()
                ->options(User::all()->pluck('name', 'id')->toArray()),

            Select::make('travel.country_id')
                ->title('CountryResponse')
                ->required()
                ->empty('Select country')
                ->options(Country::all()->pluck('name', 'id')->toArray()),

            Select::make('travel.travel_type_id')
                ->title('Travel type')
                ->required()
                ->empty('Select travel type')
                ->options(TravelType::all()->pluck('name', 'id')->toArray()),

            Select::make('travel.visible_kind')
                ->title('Visible type')
                ->required()
                ->empty('Select travel public type')
                ->options(Travel::getVisibleKindList()),
        ];
    }
}
