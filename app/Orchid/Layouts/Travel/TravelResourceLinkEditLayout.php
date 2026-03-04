<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class TravelResourceLinkEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('title')->type('title')->maxlength(255)->title('Title'),
            Input::make('path')->type('url')->maxlength(255)->title('URL'),
            Input::make('sort')->type('number')->min(0)->title('Sort'),
        ];
    }
}
