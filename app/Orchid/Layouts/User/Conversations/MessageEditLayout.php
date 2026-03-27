<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User\Conversations;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class MessageEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('message')->rows(10)->maxlength(10000)->required()->title('Message'),
        ];
    }
}