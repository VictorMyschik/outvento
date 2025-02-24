<?php

namespace App\Orchid\Layouts\References;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CategoryEquipmentEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            // continent
            Input::make('category-equipment.name')
                ->type('text')
                ->max(200)
                ->required()
                ->title('Name'),

            Input::make('category-equipment.description')
                ->type('text')
                ->max(255)
                ->title('Description'),
        ];
    }
}
