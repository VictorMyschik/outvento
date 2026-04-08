<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Album;

use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class AlbumMediaEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('sort')
                ->title('Sort')
                ->type('number'),
            TextArea::make('description')
                ->title('Description')
                ->rows(3),

            Input::make('city_country_code')->type('hidden')->id('city_country_code'),
            Input::make('city_name')->type('hidden')->id('city_name'),
            Input::make('city_lat')->type('hidden')->id('city_lat'),
            Input::make('city_lng')->type('hidden')->id('city_lng'),
            Input::make('city_place_id')->type('hidden')->id('city_place_id'),

            Group::make([
                Input::make('address_search')
                    ->title('Поиск по адресу')
                    ->placeholder('Введите город или адрес')
                    ->id('address_search'),

                Label::make('Язык поиска')->title('Язык поиска')->value($this->query->get('languageLabel')),
            ]),

            ViewField::make('')->view('admin.map-picker')->value([
                'languageCode' => $this->query->get('languageCode'),
                'lat'          => $this->query->get('lat'),
                'lng'          => $this->query->get('lng'),
            ]),

            Input::make('lat')->hidden()->id('lat'),
            Input::make('lng')->hidden()->id('lng'),
            Input::make('address')->title('Адрес')->id('address'),
        ];
    }
}