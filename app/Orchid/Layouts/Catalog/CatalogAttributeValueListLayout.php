<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use App\Models\Catalog\CatalogAttributeValue;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CatalogAttributeValueListLayout extends Table
{
    public function __construct(int $attributeId)
    {
        $this->target = 'list_attribute_' . $attributeId;
    }

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('text_value', 'Значение')->render(function (CatalogAttributeValue $value) {
                return $value->getTextValue();
            }),
            TD::make('#', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (CatalogAttributeValue $value) {
                    return DropDown::make()->icon('options-vertical')->list([
                        ModalToggle::make('редактировать')
                            ->icon('pencil')
                            ->modal('edit_attribute_value')
                            ->modalTitle('Редактировать значение')
                            ->method('saveAttributeValue', [
                                'value_id' => $value->id(), 'attribute_id' => $value->getCatalogAttributeID()
                            ]),

                        Button::make('удалить')
                            ->icon('trash')
                            ->confirm('Вы уверены, что хотите удалить это значение атрибута?')
                            ->method('deleteCatalogAttributeValue', ['value_id' => $value->id()]),
                    ]);
                }),
        ];
    }

    public function hoverable(): bool
    {
        return true;
    }
}
