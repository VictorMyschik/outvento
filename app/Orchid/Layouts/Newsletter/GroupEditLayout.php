<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use App\Services\System\Enum\Language;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Layouts\Rows;

class GroupEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Switcher::make('group.active')
                    ->sendTrueOrFalse()
                    ->title('Активно'),

                Select::make('group.language')
                    ->options(Language::getSelectList())
                    ->required()
                    ->title('Language'),
            ]),

            Input::make('group.title')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Наименование'),

            Input::make('group.code')
                ->type('text')
                ->max(255)
                ->title('Код')
        ];
    }
}
