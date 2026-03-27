<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\References;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class AddLanguageLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('code')->title('Code'),
        ];
    }
}