<?php

declare(strict_types=1);

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;

class ActionFilterPanel
{
    public static function getActionsButtons(array $properties = []): Group
    {
        return Group::make([
            Button::make('Filter')->icon('filter')->name('поиск')->method('runFiltering', $properties)->class('mr-btn-primary'),
            Button::make('Clear')->icon('close')->name('очистить')->method('clearFilter')->class('mr-btn-danger'),
        ])->autoWidth();
    }
}
