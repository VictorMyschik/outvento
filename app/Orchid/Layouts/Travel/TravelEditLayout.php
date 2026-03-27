<?php

namespace App\Orchid\Layouts\Travel;

use App\Models\Reference\Country;
use App\Models\User;
use App\Services\Travel\Enum\Activity;
use App\Services\Travel\Enum\TravelStatus;
use App\Services\Travel\Enum\TravelVisible;
use Orchid\Screen\Fields\Group;
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
            TextArea::make('travel.preview')->title('Короткое описание')->rows(5)->maxlength(500),

            Select::make('travel.status')
                ->title('Status')
                ->required()
                ->options(TravelStatus::getSelectList()),

            Select::make('travel.user_id')
                ->title('User')
                ->required()
                ->options(User::all()->pluck('name', 'id')->toArray()),

            Select::make('travel.country_id')
                ->title('Country')
                ->required()
                ->empty('Select country')
                ->fromModel(Country::class, 'name_ru'),

            Select::make('travel.travel_type_id')
                ->title('Travel type')
                ->required()
                ->empty('Select travel type')
                ->fromModel(Activity::class, 'name_ru'),

            Select::make('travel.visible_type')
                ->title('Visible type')
                ->required()
                ->empty('Select travel public type')
                ->options(TravelVisible::getSelectList()),

            Group::make([
                Input::make('travel.date_from')
                    ->title('Date from')
                    ->required()
                    ->type('date'),

                Input::make('travel.date_to')
                    ->title('Date to')
                    ->required()
                    ->type('date'),
            ])
        ];
    }
}
