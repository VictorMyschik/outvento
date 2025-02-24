<?php

namespace App\Orchid\Layouts\Language;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TranslateEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            // hide id
            /* Input::make('translate.id')
               ->value($translate->id ?? 0)
               ->type('hidden'),*/

            Input::make('translate.code')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Code'),

            Input::make('translate.translate')->title('Translate')
        ];
    }
}
