<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CatalogGoodAddNewLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('good.group_id')
                ->options($this->query->get('options', []))
                ->value(request()->get('group_id'))
                ->required()
                ->empty('Выберите группу')
                ->title('Группа'),

            Input::make('good.name')->type('text')->max(255)->required()->title('Наименование'),
        ];
    }
}
