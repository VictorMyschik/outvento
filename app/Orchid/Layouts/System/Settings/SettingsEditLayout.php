<?php

namespace App\Orchid\Layouts\System\Settings;

use App\Orchid\Screens\System\Enum\SettingsKeyEnum;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class SettingsEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Switcher::make('setup.active')
                    ->sendTrueOrFalse()
                    ->title('Active'),

                Input::make('setup.category')
                    ->type('text')
                    ->max(255)
                    ->required()
                    ->title('Category'),
            ]),

            Input::make('setup.value')
                ->type('text')
                ->required()
                ->title('Value'),

            Select::make('setup.code_key')
                ->options(SettingsKeyEnum::getSelectList())
                ->required()
                ->title('Key'),

            TextArea::make('setup.description')
                ->rows(3)
                ->title('Description'),
        ];
    }
}
