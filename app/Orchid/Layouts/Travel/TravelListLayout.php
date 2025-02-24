<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Models\Travel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class TravelListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),

            TD::make('name', 'Name')->render(fn(Travel $travel) => Link::make($travel->getName())
                ->route('travel.details', ['travel' => $travel->id()])
            ),

            TD::make('description', 'Description'),

            TD::make('status', 'Status')->render(fn(Travel $travel) => $travel->getStatusName()),

            TD::make('user_id', 'User')->render(fn(Travel $travel) => $travel->getUser()->name),

            TD::make('country', 'CountryResponse')->render(fn(Travel $travel) => $travel->getCountry()->getName()),

            TD::make('travel_type_id', 'Travel type')->render(fn(Travel $travel) => $travel->getTravelType()->getName()),


            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Travel $travel) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->type(Color::PRIMARY())
                            ->icon('pencil')
                            ->modal('travel_modal')
                            ->modalTitle('Edit travel id ' . $travel->id)
                            ->method('saveTravel')
                            ->asyncParameters(['id' => $travel->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the travel?'))
                            ->method('remove', ['id' => $travel->id]),
                    ])),
        ];
    }
}
