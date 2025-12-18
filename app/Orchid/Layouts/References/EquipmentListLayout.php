<?php

namespace App\Orchid\Layouts\References;

use App\Models\Equipment\Equipment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class EquipmentListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('name', __('Name'))->sort(),
            TD::make('description', __('Description'))->sort(),
            TD::make('', __('Category'))->render(fn(Equipment $equipment) => $equipment->getCategory()->getName())->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(Equipment $equipment) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('edit')
                            ->icon('pencil')
                            ->modal('equipment')
                            ->modalTitle('Edit equipment id ' . $equipment->id)
                            ->method('saveEquipment')
                            ->asyncParameters(['id' => $equipment->id]),

                        Button::make(__('delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the equipment?'))
                            ->method('remove', ['id' => $equipment->id]),
                    ])),
        ];
    }
}
