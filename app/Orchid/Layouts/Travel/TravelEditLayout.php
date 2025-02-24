<?php

namespace App\Orchid\Layouts\Travel;

use App\Models\Reference\Country;
use App\Models\Travel\Travel;
use App\Models\Travel\TravelType;
use App\Models\User;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\VisibleType;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TravelEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('travel.title')
                ->title('Title')
                ->required()
                ->maxlength(255),

            TextArea::make('travel.description')
                ->title('Description')
                ->rows(5)
                ->maxlength(8000),

            Select::make('travel.status')
                ->title('Status')
                ->required()
                ->options(TravelStatus::getSelectList()),

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

            Select::make('travel.visible_type')
                ->title('Visible type')
                ->required()
                ->empty('Select travel public type')
                ->options(VisibleType::getSelectList()),
        ];
    }
}
