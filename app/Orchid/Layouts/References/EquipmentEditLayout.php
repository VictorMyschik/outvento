<?php

namespace App\Orchid\Layouts\References;

use App\Models\Reference\CategoryEquipment;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class EquipmentEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('equipment.category_id')
                ->fromModel(CategoryEquipment::class, 'name')
                ->title('Category'),

            Input::make('equipment.name')
                ->type('text')
                ->max(200)
                ->required()
                ->title('Name'),

            Input::make('equipment.description')
                ->type('text')
                ->max(255)
                ->title('Description'),
        ];
    }
}
