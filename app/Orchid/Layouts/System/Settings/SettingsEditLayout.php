<?php

namespace App\Orchid\Layouts\System\Settings;

use App\Models\System\Settings;
use App\Services\System\Enum\SettingsKey;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
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

                Relation::make('setup.category')
                    ->allowAdd(true)
                    ->fromModel(Settings::class, 'category', 'category')
                    ->required()
                    ->title('Category'),
            ]),

            Input::make('setup.value')
                ->type('text')
                ->required()
                ->title('Value'),

            Select::make('setup.code_key')
                ->options($this->query->get('options', []))
                ->required()
                ->title('Key'),

            TextArea::make('setup.description')
                ->rows(3)
                ->title('Description'),
        ];
    }
}
