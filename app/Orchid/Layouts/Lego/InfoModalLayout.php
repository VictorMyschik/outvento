<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Lego;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class InfoModalLayout extends Rows
{
    public function fields(): array
    {
        return [
            ViewField::make('body')->view('admin.info'),
        ];
    }
}