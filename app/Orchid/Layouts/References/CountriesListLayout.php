<?php

namespace App\Orchid\Layouts\References;

use App\Models\Reference\Country;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CountriesListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('continent', 'Continent')->render(fn(Country $country) => $country->getContinentName())->sort(),
            TD::make('name_ru', 'RU')->sort(),
            TD::make('name_en', 'EN')->sort(),
            TD::make('name_pl', 'PL')->sort(),

            TD::make('#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Country $country) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('edit')
                            ->icon('pencil')
                            ->modal('country')
                            ->modalTitle('Edit country id ' . $country->id)
                            ->method('saveCountry')
                            ->asyncParameters(['id' => $country->id]),

                        Button::make('delete')
                            ->icon('bs.trash3')
                            ->confirm('Are you sure you want to delete the country?')
                            ->method('remove', ['id' => $country->id]),
                    ])),
        ];
    }
}
