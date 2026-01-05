<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Constructor;

use App\Orchid\Fields\CKEditor;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class ConstructorBlockItemTextEditLayout extends Rows
{
    public function fields(): array
    {
        return [
           // Input::make('item.title')->type('text')->max(255)->title('Заголовок'),
            ViewField::make('')->view('space'),
            ViewField::make('')->view('admin.h6')->value('Текст'),
            CKEditor::make('item.text'),
            ViewField::make('')->view('space'),
            Input::make('item.sort')->type('number')->min(0)->max(999)->title('Сортировка')
        ];
    }
}
