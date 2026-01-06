<?php

namespace App\Orchid\Layouts\Language;

use App\Services\Language\Enum\TranslateGroupEnum;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
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

            TextArea::make('translate.ru')
                ->type('text')
                ->max(255)
                ->rows(5)
                ->required()
                ->title('RU'),

            TextArea::make('translate.en')
                ->type('text')
                ->max(255)
                ->rows(5)
                ->required()
                ->title('EN'),

            TextArea::make('translate.pl')
                ->type('text')
                ->max(255)
                ->rows(5)
                ->required()
                ->title('PL'),

        ];
    }
}
