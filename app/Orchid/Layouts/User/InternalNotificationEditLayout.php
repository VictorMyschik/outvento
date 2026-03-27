<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class InternalNotificationEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('notification.title')->type('text')->max(255)->required()->title('Title'),
            TextArea::make('notification.message')->rows(10)->required()->title('Message'),
        ];
    }
}
