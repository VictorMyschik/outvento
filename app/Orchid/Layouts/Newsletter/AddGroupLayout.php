<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class AddGroupLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('group_ids')
                ->options($this->query->get('options', []))
                ->title('Добавление группы')
                ->multiple()
                ->required(),
        ];
    }
}
