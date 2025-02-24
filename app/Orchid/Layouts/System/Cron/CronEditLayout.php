<?php

namespace App\Orchid\Layouts\System\Cron;

use App\Orchid\Screens\System\Enum\CronKeyEnum;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class CronEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Switcher::make('cron.active')
                ->sendTrueOrFalse()
                ->title('Active'),

            Select::make('cron.cron_key')
                ->options(CronKeyEnum::getSelectList())
                ->required()
                ->title('Key'),

            Input::make('cron.period')
                ->type('number')
                ->min(0)
                ->max(9000000)
                ->title('Period in minutes')
                ->required(),

            TextArea::make('cron.description')->title('Description')->rows(5),
        ];
    }
}
