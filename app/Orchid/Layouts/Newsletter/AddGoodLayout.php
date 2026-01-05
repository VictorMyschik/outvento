<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Newsletter;

use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class AddGoodLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('good_ids')
                ->options($this->query->get('options', []))
                ->title('Добавление продукта')
                ->multiple()
                ->required(),
        ];
    }
}
