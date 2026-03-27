<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use App\Models\Reference\City;
use App\Services\System\Enum\Language;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CityListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('country_id', 'Country')->render(fn(City $city) => $city->getCountry()->getName(Language::RU))->sort(),
            TD::make('name', 'Name')->sort(),
            TD::make('timezone', 'Timezone')->sort(),
            TD::make('lat', 'Lat')->sort(),
            TD::make('lng', 'Lng')->sort(),
            TD::make('place_id', 'Place_id')->sort(),


            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(City $city) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the city?'))
                            ->method('remove', ['id' => $city->id]),
                    ])),
        ];
    }
}
