<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Catalog;

use App\Jobs\Catalog\SearchGoodsByCatalogGroupJob;
use App\Models\Catalog\CatalogGroup;
use App\Orchid\Filters\Catalog\CatalogTypeFilter;
use App\Orchid\Layouts\Catalog\CatalogGroupEditLayout;
use App\Orchid\Layouts\Catalog\CatalogGroupListLayout;
use App\Services\Catalog\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class CatalogGroupsScreen extends Screen
{
    protected string $name = 'Список групп каталога';

    public function __construct(private Request $request, private readonly CatalogService $service) {}

    public function query(): iterable
    {
        return [
            'list' => CatalogTypeFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('добавить')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('type_modal')
                ->modalTitle('Создать новый тип')
                ->method('saveCatalogGroup', ['groupId' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            CatalogTypeFilter::displayFilterCard($this->request),
            CatalogGroupListLayout::class,
            Layout::modal('type_modal', CatalogGroupEditLayout::class)->async('asyncGetType'),
        ];
    }

    public function asyncGetType(int $groupId = 0): array
    {
        return [
            'type' => CatalogGroup::loadBy($groupId),
        ];
    }

    public function saveCatalogGroup(Request $request, int $groupId): void
    {
        $input = Validator::make($request->all(), [
            'type.name'      => 'required|string',
            'type.json_link' => 'nullable|string',
        ])->validate()['type'];

        $this->service->saveCatalogGroup($groupId, $input);
    }

    public function remove(int $groupId): void
    {
        $this->service->deleteCatalogType($groupId);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $input = $request->all(CatalogTypeFilter::FIELDS);

        $list = [];
        foreach (CatalogTypeFilter::FIELDS as $item) {
            if (!is_null($input[$item])) {
                $list[$item] = $input[$item];
            }
        }

        return redirect()->route('type.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('type.list');
    }

    #endregion

    public function updateGoods(int $groupId): void
    {
        SearchGoodsByCatalogGroupJob::dispatch($groupId);
    }
}
