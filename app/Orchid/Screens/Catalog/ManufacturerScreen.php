<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Catalog;

use App\Orchid\Filters\Catalog\ManufacturerFilter;
use App\Orchid\Layouts\Catalog\ManufacturerEditLayout;
use App\Orchid\Layouts\Catalog\ManufacturerListLayout;
use App\Services\Catalog\Onliner\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ManufacturerScreen extends Screen
{
    protected $name = 'Список производителей';

    public function __construct(private readonly Request $request, private readonly CatalogService $service) {}

    public function query(): iterable
    {
        return [
            'list' => ManufacturerFilter::runQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить')->class('mr-btn-success')
                ->modal('edit_manufacturer_modal')->modalTitle('Добавить производителя')
                ->method('saveManufacturer')->parameters(['manufacturer_id' => 0])->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ManufacturerFilter::displayFilterCard($this->request),
            ManufacturerListLayout::class,
            Layout::modal('edit_manufacturer_modal', ManufacturerEditLayout::class)->async('asyncGetManufacturer'),
        ];
    }

    public function asyncGetManufacturer(int $manufacturer_id = 0): array
    {
        return ['manufacturer' => $this->service->getManufacturer($manufacturer_id)];
    }

    public function saveManufacturer(Request $request, int $manufacturer_id): void
    {
        $input = Validator::make($request->all(), [
            'manufacturer.name'    => 'required|string|max:255',
            'manufacturer.address' => 'nullable|string|max:255',
        ])->validate()['manufacturer'];

        $this->service->saveManufacturer($manufacturer_id, $input);
    }

    public function remove(int $manufacturer_id): void
    {
        $this->service->deleteManufacturer($manufacturer_id);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $input = $request->all(ManufacturerFilter::FIELDS);

        $list = [];
        foreach (ManufacturerFilter::FIELDS as $item) {
            if (!is_null($input[$item])) {
                $list[$item] = $input[$item];
            }
        }

        return redirect()->route('manufacturer.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('manufacturer.list');
    }
    #endregion
}
