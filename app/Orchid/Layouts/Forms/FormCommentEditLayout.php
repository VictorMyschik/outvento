<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Forms;

use App\Orchid\Fields\CKEditor;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Layouts\Rows;

class FormCommentEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Switcher::make('form.active')->sendTrueOrFalse()->title('Пометит как прочитанная заявка'),
            CKEditor::make('form.description')
                ->title('Свой комментарий к заявке'),
        ];
    }
}
