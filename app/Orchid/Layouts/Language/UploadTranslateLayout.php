<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Language;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class UploadTranslateLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('Загрузить Excel')->type('file')->name('file')->accept('.xls, .xlsx'),
        ];
    }
}

