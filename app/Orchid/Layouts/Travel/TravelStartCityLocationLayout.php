<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class TravelStartCityLocationLayout extends Rows
{
    public function fields(): array
    {
        return [
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
        ];
    }
}