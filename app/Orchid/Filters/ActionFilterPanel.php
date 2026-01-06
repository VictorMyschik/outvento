<?php

namespace App\Orchid\Filters;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;

class ActionFilterPanel
{
    public static function getActionsButtons(): Group
    {
        return Group::make([
            Button::make('Filter')->method('runFiltering')->class('mr-btn-success'),
            Button::make('Clear')->method('clearFilter')->class('mr-btn-danger'),
        ])->autoWidth();
    }
}
