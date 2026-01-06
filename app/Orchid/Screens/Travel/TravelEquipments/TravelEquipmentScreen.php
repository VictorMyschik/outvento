<?php

namespace App\Orchid\Screens\Travel\TravelEquipments;

use App\Models\Travel\Travel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Color;

class TravelEquipmentScreen extends Screen
{
    public ?Travel $travel = null;

    public function name(): ?string
    {
        $name = 'Equipments';
        $name .= $this->travel->getName();

        return $name;
    }

    public function description(): ?string
    {
        $name = 'Equipments';
        $name .= $this->travel->getName();

        return $name;
    }

    public function query(Travel $travel): array
    {
        return [
            'equipments' => $travel,
        ];
    }

    public function commandBar(): iterable
    {
        $id = (int)$this->travel?->id();

        return [
            ModalToggle::make('Edit')
                ->type(Color::BASIC())
                ->icon('pencil')
                ->modal('travel_modal')
                ->modalTitle('Edit travel id')
                ->method('saveTravel')
                ->asyncParameters(['id' => $id]),

            Button::make(__('Delete'))
                ->icon('bs.trash3')
                ->confirm(__('Are you sure you want to delete the travel?'))
                ->method('remove', ['id' => $id]),
        ];
    }

    public function layout(): iterable
    {
        return [];
    }

    public function asyncGetTravel(int $id = 0): array
    {
        return [
            'travel' => Travel::loadBy($id) ?: new Travel()
        ];
    }
}
