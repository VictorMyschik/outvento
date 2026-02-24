<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Orchid\Fields\CKEditor;
use App\Services\Travel\Enum\TravelPointType;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class TravelStartCityLocationLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('type')
            ->options(TravelPointType::getSelectList())
            ->title('Тип локации'),

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
                ->id('start_address'),

            CKEditor::make('description')
        ];
    }
}