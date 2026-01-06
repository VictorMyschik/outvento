<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Constructor;

use App\Orchid\Fields\CKEditor;
use App\Orchid\Rebuild\MrUpload;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class ConstructorBlockItemSliderEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('item.title')->title('Заголовок')->placeholder('Введите заголовок (можно оставить пустым)'),
            CKEditor::make('item.description')->rows(5)->title('Описание'),
            Input::make('item.sort')->type('number')->min(0)->max(999)->title('Сортировка'),
            MrUpload::make('item.images')->groups('photo')->acceptedFiles('image/*'),
        ];
    }
}
