<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Lego;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class AvatarUploadLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('avatar')->required()->type('file'),
        ];
    }
}
