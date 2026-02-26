<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Models\Travel\Travel;
use App\Services\System\Enum\Language;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TravelListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),

            TD::make('title', 'Title')->render(fn(Travel $travel) => Link::make($travel->getTitle())
                ->route('travel.details', ['travel' => $travel->id()])
            )->sort(),

            TD::make('status', 'Status')->render(fn(Travel $travel) => $travel->getStatus()->getLabel())->sort(),
            TD::make('members', 'Max members')->sort(),
            TD::make('user_id', 'User')->render(fn(Travel $travel) => $travel->getOwner()->name),
            TD::make('travel_type_id', 'Travel type')->render(fn(Travel $travel) => $travel->getActivities()->getName(Language::RU)),


            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Travel $travel) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
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

    public function hoverable(): bool
    {
        return true;
    }

    public function striped(): bool
    {
        return true;
    }
}
