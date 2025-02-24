<?php

namespace App\Orchid\Layouts\Language;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Layouts\Rows;

class LanguageEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Switcher::make('language.active')->sendTrueOrFalse()->title('Active'),

            Input::make('language.code')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Code'),

            Input::make('language.name')->title('Name')
        ];
    }
}
