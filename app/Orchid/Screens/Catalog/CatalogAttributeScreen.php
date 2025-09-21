<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Catalog;

use App\Models\Catalog\CatalogAttribute;
use App\Models\Catalog\CatalogAttributeValue;
use App\Models\Catalog\CatalogGroup;
use App\Orchid\Filters\Catalog\CatalogAttributeGroupFilter;
use App\Orchid\Filters\Catalog\CatalogAttributeValueFilter;
use App\Orchid\Layouts\Catalog\CatalogAttributeEditLayout;
use App\Orchid\Layouts\Catalog\CatalogAttributeValueEditLayout;
use App\Orchid\Layouts\Catalog\CatalogAttributeValueListLayout;
use App\Services\Catalog\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CatalogAttributeScreen extends Screen
{
    public ?CatalogGroup $group = null;

    public function __construct(
        private readonly CatalogService $catalogService,
    ) {}

    public function name(): ?string
    {
        return 'Атрибуты для ' . $this->group->getName();
    }

    public function query(int $group_id): iterable
    {
        $this->group = $this->catalogService->getGroupById($group_id);

        $attributeList = CatalogAttributeGroupFilter::runQuery($group_id);
        foreach ($attributeList->all() as $attribute) {
            $out['list_attribute_' . $attribute->id()] = CatalogAttributeValueFilter::runQuery($attribute->id());
        }

        $out['list_attribute'] = $attributeList;

        return $out;
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить атрибут')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('edit_attribute')
                ->modalTitle('Добавить атрибут')
                ->method('saveAttribute', ['attribute_id' => 0, 'group_id' => $this->group->id()]),

            Link::make('Назад')
                ->icon('arrow-left')
                ->href(request()->headers->get('referer') ?: route('catalog.attributes.list')),
        ];
    }

    public function layout(): iterable
    {
        $out = [];

        $attributes = $this->query($this->group->id())['list_attribute']->all();

        $list = [];
        /** @var CatalogAttribute $value */
        foreach ($attributes as $attribute) {
            $list[$this->getAccordionTitle($attribute)] = [
                Layout::rows([
                    Group::make([
                        ModalToggle::make('Добавить значение')
                            ->class('mr-btn-success')
                            ->icon('plus')
                            ->modal('edit_attribute_value')
                            ->modalTitle('Добавить значение атрибута')
                            ->method('saveAttributeValue', ['value_id' => 0, 'attribute_id' => $attribute->id()]),
                        ModalToggle::make('редактировать атрибут')
                            ->class('mr-btn-success')
                            ->icon('pencil')
                            ->modal('edit_attribute')
                            ->modalTitle('Редактировать атрибут')
                            ->method('saveAttribute', ['attribute_id' => $attribute->id(), 'group_id' => $this->group->id()]),
                        Button::make('удалить')
                            ->icon('trash')
                            ->class('mr-btn-danger')
                            ->confirm('Будет удален атрибут со всем содержимым. Удалить?')
                            ->method('deleteCatalogAttribute', ['attribute_id' => $attribute->id()])
                    ])->autoWidth()
                ]),
                new CatalogAttributeValueListLayout($attribute->id())
            ];
        }

        $out[] = Layout::accordion($list);

        $out[] = Layout::modal('edit_attribute', CatalogAttributeEditLayout::class)->async('asyncGetAttribute')->size(Modal::SIZE_LG);
        $out[] = Layout::modal('edit_attribute_value', CatalogAttributeValueEditLayout::class)->async('asyncGetAttributeValue');

        return $out;
    }

    private function getAccordionTitle(CatalogAttribute $attribute): string
    {
        $title = '<div>' . $attribute->getName() . '</div>';

        $title .= '<div class="text-muted small">ID:' . $attribute->id() . ' Сорт.: ' . $attribute->getSort() . '</div>';
        $title .= '<div class="text-muted small">' . $attribute->getDescription() . '</div>';


        return $title;
    }

    public function asyncGetAttribute(int $attribute_id = 0): array
    {
        return [
            'attribute' => CatalogAttribute::loadBy($attribute_id),
        ];
    }

    public function asyncGetAttributeValue(int $value_id = 0, int $attribute_id = 0): array
    {
        $catalogAttributeValue = CatalogAttributeValue::loadBy($value_id) ?: new CatalogAttributeValue();
        $attribute_id && $catalogAttributeValue->setCatalogAttributeID($attribute_id);

        return [
            'attribute_value' => $catalogAttributeValue,
            'attribute_id'    => $attribute_id,
        ];
    }

    public function saveAttributeValue(Request $request, int $value_id = 0): void
    {
        $input = Validator::make($request->all(), [
            'attribute_value.text_value'           => 'required|string|max:255',
            'attribute_value.catalog_attribute_id' => 'required|integer|exists:catalog_attributes,id',
        ])->validate()['attribute_value'];

        $this->catalogService->saveAttributeValue($value_id, $input);

        Toast::info('Значение сохранено')->delay(1000);
    }

    public function deleteCatalogAttributeValue(int $value_id): void
    {
        $this->catalogService->deleteAttributeValue($value_id);
        Toast::info('Значение удалено')->delay(1000);
    }

    public function saveAttribute(Request $request, int $attribute_id = 0, int $group_id = 0): void
    {
        $input = Validator::make($request->all(), [
            'attribute.name'        => 'required|string|max:255|unique:catalog_attributes,name,' . $attribute_id . ',id,group_attribute_id,' . $group_id,
            'attribute.description' => 'nullable|string|max:8000',
            'attribute.sort'        => 'nullable|min:0|max:999',
        ])->validate()['attribute'];

        $input['group_attribute_id'] = $group_id;
        $input['sort'] = (int)$input['sort'];
        $this->catalogService->saveAttribute($attribute_id, $input);

        Toast::info('Атрибут сохранён')->delay(1000);
    }

    public function deleteCatalogAttribute(int $attribute_id): void
    {
        $this->catalogService->deleteAttribute($attribute_id);
        Toast::info('Атрибут удалён')->delay(1000);
    }
}
