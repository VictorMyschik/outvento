<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use App\Orchid\Fields\CKEditor;
use Orchid\Screen\Layouts\Rows;

class TravelCommentEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            CKEditor::make('comment.content')->title('Title'),
        ];
    }
}
