<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Orchid\Fields\CKEditor;
use App\Services\Travel\Enum\TravelPointType;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class TravelStartCityLocationLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('city_country_code')->type('hidden')->id('city_country_code'),
            Input::make('city_name')->type('hidden')->id('city_name'),
            Input::make('city_lat')->type('hidden')->id('city_lat'),
            Input::make('city_lng')->type('hidden')->id('city_lng'),
            Input::make('city_place_id')->type('hidden')->id('city_place_id'),

            Group::make([
                Select::make('type')
                    ->options(TravelPointType::getSelectList())
                    ->title('Тип локации'),

                Input::make('position')
                    ->type('number')
                    ->title('Сортировка')
                    ->help('Используется при прокладке маршрута или сортировки локаций'),
            ]),

            Input::make('address_search')
                ->title('Поиск по адресу')
                ->placeholder('Введите город или адрес')
                ->id('address_search'),

            ViewField::make('')->view('admin.map-picker'),

            Input::make('start_lat')
                ->type('hidden')
                ->id('start_lat'),

            Input::make('start_lng')
                ->type('hidden')
                ->id('start_lng'),

            Input::make('start_address')
                ->title('Адрес')
                ->id('start_address'),


            ViewField::make('')->view('space'),
            CKEditor::make('description')->title('Описание')
        ];
    }
}
