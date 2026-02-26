<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class InviteByEmailEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('email')->type('email')->maxlength(255)->required()->title('Set Email')->help('Email of user to invite'),
        ];
    }
}
