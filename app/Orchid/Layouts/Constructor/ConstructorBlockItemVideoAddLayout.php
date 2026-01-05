<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Constructor;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class ConstructorBlockItemVideoAddLayout extends Rows
{
    public function fields(): array
    {
        return [
            //Input::make('item.title')->title('Заголовок')->placeholder('Введите заголовок (можно оставить пустым)'),
            Input::make('item.file')->type('file')->required()->title('Файл'),
            TextArea::make('item.description')->rows(5)->title('Описание'),
            Input::make('item.sort')->type('number')->min(0)->max(999)->title('Сортировка')
        ];
    }
}