<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Constructor;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class ConstructorBlockEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('block.title')->type('text')->max(255)->required()->title('Заголовок'),
            Input::make('block.sort')->type('number')->min(0)->max(999)->title('Сортировка блока'),
        ];
    }
}