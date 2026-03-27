<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Travel;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class AddTravelModalLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('title')
                ->title('Title')
                ->required()
                ->maxlength(255),

            TextArea::make('preview')
                ->title('Короткое описание')
                ->rows(5)
                ->maxlength(350),
        ];
    }
}
