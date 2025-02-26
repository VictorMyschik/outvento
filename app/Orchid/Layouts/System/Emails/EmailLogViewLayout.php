<?php

namespace App\Orchid\Layouts\System\Emails;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class EmailLogViewLayout extends Rows
{
    public function fields(): array
    {
        return [
            ViewField::make('body')->view('emails.log'),
        ];
    }
}