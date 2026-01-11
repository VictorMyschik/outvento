<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\System;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class FileUploadLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('file')->type('file')->required(),
        ];
    }
}
