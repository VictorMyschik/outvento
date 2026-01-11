<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use App\Models\Catalog\CatalogAttribute;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CatalogAttributeValueEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Select::make('attribute_value.catalog_attribute_id')
                ->fromModel(CatalogAttribute::class, 'name', 'id')
                ->empty('Выберите атрибут')
                ->title('Атрибут')
                ->required(),
            Input::make('attribute_value.text_value')->title('Значение'),
        ];
    }
}
