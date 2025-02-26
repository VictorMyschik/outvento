<?php

namespace App\Orchid\Layouts\Lego;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;

class ActionDeleteModelLayout
{
    public static function getActionButtons(string $title, string $methodName = 'remove', array $parameters = []): Group
    {
        return Group::make([
            Button::make('Clear')->confirm('Удалить?')->class('mr-btn-danger')->name($title)->method($methodName)->parameters($parameters)->novalidate(),
        ])->autoWidth();
    }
}
