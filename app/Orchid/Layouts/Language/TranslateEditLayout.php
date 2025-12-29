<?php

namespace App\Orchid\Layouts\Language;

use App\Services\Language\Enum\TranslateGroupEnum;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class TranslateEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('groups_selected')
                ->options(TranslateGroupEnum::getSelectList())
                ->title('Translate Group')
                ->multiple()
                ->value($this->query->get('groups_selected', []))
                ->empty(),

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
