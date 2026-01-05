<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use App\Models\News\NewsGroup;
use App\Services\System\Enum\Language;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class SubgroupEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Select::make('subgroup.group_id')
                    ->fromModel(NewsGroup::class, 'title')
                    ->title('Группа новостей')
                    ->required(),
                Input::make('subgroup.code')->type('text')->max(255)->required()->title('Код'),
            ]),

            Input::make('subgroup.title')->type('text')->max(255)->required()->title('Наименование'),
        ];
    }
}
