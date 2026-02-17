<?php

namespace App\Orchid\Screens\Travel;

use App\Models\Travel\Travel;
use App\Orchid\Layouts\Travel\TravelEditLayout;
use App\Orchid\Layouts\Travel\TravelListLayout;
use App\Services\Travel\TravelService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TravelListScreen extends Screen
{
    public function __construct(private readonly TravelService $service) {}

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
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('travel_modal')
                ->modalTitle('Create Travel')
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

    public function saveTravel(Request $request, int $id): void
    {
        $data = $request->validate([
            'travel.title'          => 'required|string|max:255',
            'travel.preview'        => 'nullable|string',
            'travel.status'         => 'required|integer',
            'travel.user_id'        => 'required|integer',
            'travel.country_id'     => 'required|integer',
            'travel.travel_type_id' => 'required|integer',
            'travel.visible_type'   => 'required|integer',
            'travel.date_from'      => 'required|date|before:travel.date_to',
            'travel.date_to'        => 'required|date|after:travel.date_from',
        ])['travel'];

        $data['preview'] = substr($data['preview'], 0, 350);
        if ($id > 0) {
            $this->service->updateTravel($id, $data);
        } else {
            $this->service->createTravel($data);
        }

        Toast::info('Travel was saved');
    }

    public function remove(int $id): void
    {
        Travel::loadBy($id)?->delete();
    }
}
