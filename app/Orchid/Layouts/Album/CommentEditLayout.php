<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Album;

use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class CommentEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            TextArea::make('message')
                ->title('Comment')
                ->rows(5),
        ];
    }
}