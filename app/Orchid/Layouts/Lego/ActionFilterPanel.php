<?php

namespace App\Orchid\Layouts\Lego;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;

class ActionFilterPanel
{
    public static function getActionsButtons(array $properties = []): Group
    {
        return Group::make([
            Button::make('Filter')->icon('filter')->name('поиск')->method('runFiltering', $properties)->class('mr-btn-success'),
            Button::make('Clear')->icon('close')->name('очистить')->method('clearFilter')->class('mr-btn-route'),
        ])->autoWidth();
    }
}
