<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CatalogGroupEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('type.name')->type('text')->max(255)->required()->title('Наименование'),
            Input::make('type.json_link')->type('text')->max(255)->required()->title('Ссылка на Json данные'),
        ];
    }
}
