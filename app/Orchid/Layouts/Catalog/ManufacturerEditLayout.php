<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class ManufacturerEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('manufacturer.name')->type('text')->max(255)->required()->title('Наименование'),
            Input::make('manufacturer.address')->type('text')->max(255)->title('Адрес'),
        ];
    }
}
