<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class CatalogGoodSortEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('sort')->type('number')->min(0)->max(999)->title('Сортировка'),
        ];
    }
}