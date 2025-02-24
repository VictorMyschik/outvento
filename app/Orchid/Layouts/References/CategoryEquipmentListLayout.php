<?php

namespace App\Orchid\Layouts\References;

use App\Models\Reference\CategoryEquipment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CategoryEquipmentListLayout extends Table
{
    public $target = 'list';

    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->sort(),
            TD::make('name', __('Name'))->render(fn(CategoryEquipment $categoryEquipment) => Link::make($categoryEquipment->getName())
                ->route('reference.equipments.list', ['category[0]' => $categoryEquipment->getName()]))->sort(),
            TD::make('description', __('Description'))->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(CategoryEquipment $categoryEquipment) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->modal('category_equipment')
                            ->modalTitle('Edit category equipment id ' . $categoryEquipment->id)
                            ->method('saveCategoryEquipment')
                            ->asyncParameters(['id' => $categoryEquipment->id]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete the category equipment?'))
                            ->method('remove', ['id' => $categoryEquipment->id]),
                    ])),
        ];
    }
}
