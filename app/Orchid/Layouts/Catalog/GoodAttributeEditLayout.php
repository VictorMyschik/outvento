<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Catalog;

use App\Models\Catalog\CatalogAttribute;
use App\Models\Catalog\CatalogAttributeValue;
use App\Models\Catalog\CatalogGroupAttribute;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class GoodAttributeEditLayout extends Listener
{
    protected $targets = [
        'groupId',
        'catalogGroupId',
        'attributeId',
    ];

    protected function layouts(): iterable
    {
        $goodGroupId = (int)$this->query->get('catalogGroupId');
        $attributeGroupId = (int)$this->query->get('groupId');
        $attributeValueId = (int)$this->query->get('attributeId');

        return [
            Layout::rows([
                Input::make('catalogGroupId')->value($goodGroupId)->type('hidden'),

                Select::make('groupId')
                    ->fromQuery(CatalogGroupAttribute::where('group_id', $goodGroupId), 'name', 'id')
                    ->title('Группа атрибутов')
                    ->empty('Выберите группу атрибутов')
                    ->required(),

                Select::make('attributeId')
                    ->fromQuery(CatalogAttribute::where('group_attribute_id', $attributeGroupId), 'name', 'id')
                    ->empty('Выберите атрибут')
                    ->title('Атрибут'),

                Select::make('attributeValueId')
                    ->fromQuery(CatalogAttributeValue::where('catalog_attribute_id', $attributeValueId), 'text_value', 'id')
                    ->empty('Выберите атрибут')
                    ->title('Значение'),

                Select::make('booleanValue')
                    ->options([
                        '1' => 'Да',
                        '0' => 'Нет',
                    ])
                    ->empty('Не выбрано')
                    ->title('Булево значение'),
            ]),
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository
            ->set('catalogGroupId', $request->input('catalogGroupId'))
            ->set('groupId', $request->input('groupId'))
            ->set('attributeId', $request->input('attributeId'));
    }
}
