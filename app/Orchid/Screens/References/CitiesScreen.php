<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\Reference\City;
use App\Orchid\Layouts\References\CityEditLayout;
use App\Orchid\Layouts\References\CityListLayout;
use App\Services\References\ReferenceService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CitiesScreen extends Screen
{
    public string $name = 'Справочник городов';

    public function __construct(private ReferenceService $service) {}

    public function query(): iterable
    {
        return [
            'list' => City::filters([])->paginate(50)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('city')
                ->modalTitle('Create New City')
                ->method('saveCity')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            CityListLayout::class,
            Layout::modal('city', CityEditLayout::class)->async('asyncGetCity'),
        ];
    }

    public function asyncGetCity(int $id = 0): array
    {
        return [
            'city' => City::loadBy($id) ?: new City()
        ];
    }

    public function saveCity(Request $request, int $id): void
    {
        $data = $request->validate([
            'city.country_id' => 'required|int',
            'city.name_ru'    => 'required|string',
            'city.name_en'    => 'required|string',
            'city.name_pl'    => 'required|string',
        ])['city'];

        $this->service->saveCity($id, $data);
    }

    public function remove(int $id): void
    {
        try {
            City::loadBy($id)?->delete();
        } catch (\Exception $e) {
            Toast::error($e->getMessage());
        }
    }
}
