<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Travel;

use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class DescriptionPointShowLayout extends Rows
{
    public function fields(): array
    {
        return [
            ViewField::make('')->view('admin.raw')->value($this->query->get('description')),
        ];
    }
}