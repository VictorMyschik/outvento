<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class RenameFileEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('attachment.name')->maxlength(255)->title('Name')->required(),
        ];
    }
}