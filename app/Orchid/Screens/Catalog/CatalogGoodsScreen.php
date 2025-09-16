<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Catalog;

use App\Models\Catalog\CatalogGood;
use App\Orchid\Filters\Catalog\CatalogGoodsFilter;
use App\Orchid\Layouts\Catalog\CatalogGoodAddNewLayout;
use App\Orchid\Layouts\Catalog\GoodListLayout;
use App\Orchid\Layouts\Lego\InfoModalLayout;
use App\Services\Catalog\Onliner\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class CatalogGoodsScreen extends Screen
{
    protected string $name = 'Список товаров';

    public function __construct(private readonly Request $request, private readonly CatalogService $service) {}

    public function query(): iterable
    {
        return [
            'list' => CatalogGoodsFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить')->class('mr-btn-success')
                ->modal('add_good_modal')->modalTitle('Добавить товар')
                ->method('createGood')->parameters(['group_id' => $this->request->get('group_id')])->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            CatalogGoodsFilter::displayFilterCard($this->request),
            GoodListLayout::class,
            Layout::modal('view_good', InfoModalLayout::class)->async('asyncGetGood')->size(Modal::SIZE_XL),
            Layout::modal('add_good_modal', CatalogGoodAddNewLayout::class)->async('asyncNewGood'),
        ];
    }

    public function asyncGetGood(int $id = 0): array
    {
        return ['body' => CatalogGood::loadByOrDie($id)->sl];
    }

    public function asyncNewGood(int $group_id = 0): array
    {
        $options = [];
        foreach ($this->service->getCatalogGroupList() as $item) {
            $options[$item['id']] = $item['name'];
        }
        return ['options' => $options, 'group_id' => $group_id];
    }

    public function createGood(Request $request): void
    {
        $input = Validator::make($request->all(), [
            'good.active'   => 'required|boolean',
            'good.group_id' => 'required|exists:catalog_groups,id',
            'good.name'     => 'required|string|max:255',
        ])->validate()['good'];

        $this->service->saveGood(0, $input);
    }

    public function remove(int $id): void
    {
        $this->service->deleteGood($id);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $input = $request->all(CatalogGoodsFilter::FIELDS);

        $list = [];
        foreach (CatalogGoodsFilter::FIELDS as $item) {
            if (!is_null($input[$item])) {
                $list[$item] = $input[$item];
            }
        }

        return redirect()->route('goods.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('goods.list');
    }
    #endregion
}
