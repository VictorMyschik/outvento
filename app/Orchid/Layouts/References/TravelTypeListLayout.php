<?php

namespace App\Orchid\Layouts\References;

use App\Models\Travel\TravelType;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TravelTypeListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('#', 'Image')->render(function (TravelType $travelType) {
                return View('admin.image')->with(['path' => $travelType->getImageUrl()]);
            }),
            TD::make('name_ru', 'RU')->sort(),
            TD::make('name_en', 'EN')->sort(),
            TD::make('name_pl', 'PL')->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(TravelType $travelType) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('travel_type')
                            ->modalTitle('Edit type id ' . $travelType->id)
                            ->method('saveTravelType')
                            ->asyncParameters(['id' => $travelType->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the travel type?'))
                            ->method('remove', ['id' => $travelType->id]),
                    ])),
        ];
    }
}
