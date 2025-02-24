<?php

namespace App\Orchid\Screens\Travel;

use App\Models\Travel;
use App\Orchid\Layouts\Travel\TravelEditLayout;
use App\Orchid\Layouts\Travel\TravelListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TravelListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'list' => Travel::filters([])->paginate(20)
        ];
    }

    public function name(): ?string
    {
        return 'Походы';
    }

    public function description(): ?string
    {
        return "Список походов";
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->type(Color::PRIMARY())
                ->icon('plus')
                ->modal('travel_modal')
                ->modalTitle('Create New Travel')
                ->method('saveTravel')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            TravelListLayout::class,
            Layout::modal('travel_modal', TravelEditLayout::class)->async('asyncGetTravel'),
        ];
    }

    public function asyncGetTravel(int $id = 0): array
    {
        return [
            'travel' => Travel::loadBy($id) ?: new Travel()
        ];
    }

    public function saveTravel(Request $request): void
    {
        $data = $request->validate([
            'travel.name'           => 'required|string|max:255',
            'travel.description'    => 'nullable|string|max:8000',
            'travel.status'         => 'required|integer',
            'travel.user_id'        => 'required|integer',
            'travel.country_id'     => 'required|integer',
            'travel.travel_type_id' => 'required|integer',
        ])['travel'];

        $travel = Travel::loadBy($request->get('id')) ?: new Travel();
        $travel->fill($data);
        $travel->save_mr();

        Toast::info('Travel was saved');
    }

    public function remove(int $id): void
    {
        Travel::loadBy($id)?->delete();
    }
}
