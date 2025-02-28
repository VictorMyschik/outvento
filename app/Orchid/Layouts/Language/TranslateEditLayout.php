<?php

namespace App\Orchid\Layouts\Language;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TranslateEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('translate.code')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Code'),

            Input::make('translate.ru')
                ->type('text')
                ->max(255)
                ->required()
                ->title('RU'),

            Input::make('translate.en')
                ->type('text')
                ->max(255)
                ->required()
                ->title('EN'),

            Input::make('translate.pl')
                ->type('text')
                ->max(255)
                ->required()
                ->title('PL'),
        ];
    }
}
