<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Constructor;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class ConstructorBlockItemSlideEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('item.display_name')->title('Заголовок')->placeholder('Наименование картинки'),
            Input::make('item.alt')->title('Alt'),
            Input::make('item.sort')->type('number')->min(0)->max(999)->title('Сортировка'),
        ];
    }
}