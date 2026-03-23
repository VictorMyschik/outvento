<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\Reference\Country;
use App\Orchid\Layouts\References\CountriesListLayout;
use App\Orchid\Layouts\References\CountryEditLayout;
use App\Services\References\AbstractReferenceService;
use App\Services\References\CountryService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class CountryScreen extends Screen
{
    public string $name = 'Countries';
    public string $description = 'List of countries available in the system';

    public function __construct(private readonly CountryService $service) {}

    public function query(): iterable
    {
        return [
            'list' => Country::filters([])->paginate(300)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('country')
                ->modalTitle('Create New Country')
                ->method('saveCountry')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            CountriesListLayout::class,
            Layout::modal('country', CountryEditLayout::class)->async('asyncGetCountry'),
        ];
    }

    public function asyncGetCountry(int $id): array
    {
        return [
            'country' => Country::loadBy($id),
        ];
    }

    public function saveCountry(Request $request, int $id): void
    {
        $data = $request->validate([
            'country.continent'      => 'required|int',
            'country.name_ru'        => 'required|string',
            'country.name_en'        => 'required|string',
            'country.name_pl'        => 'required|string',
            'country.iso3166alpha2'  => 'required|string|max:2',
            'country.iso3166alpha3'  => 'required|string|max:3',
            'country.iso3166numeric' => 'required|string|max:3',
        ])['country'];

        $this->service->saveCountry($id, $data);
    }
}
