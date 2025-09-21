<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class CatalogAttributeEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('attribute.name')->type('text')->max(255)->required()->title('Наименование'),
            TextArea::make('attribute.description')->rows(5)->name('attribute.description')->title('Краткое описание'),
            ViewField::make('')->view('space'),
            Input::make('attribute.sort')->type('number')->min(0)->max(999)->title('Сортировка'),
        ];
    }
}
