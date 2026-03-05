<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TravelResourceFileEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('resource.title')->type('title')->maxlength(255)->title('Title'),
            Input::make('resource.file')->type('file')->title('File'),
            Input::make('resource.sort')->type('number')->min(0)->title('Sort'),
        ];
    }
}
