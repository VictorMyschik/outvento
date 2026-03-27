<?php

namespace App\Orchid\Layouts\Lego;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class InfoRawModalLayout extends Rows
{
    public function fields(): array
    {
        return [
            ViewField::make('')->view('admin.raw')->value($this->query->get('view')),
        ];
    }
}