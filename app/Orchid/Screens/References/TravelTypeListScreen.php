<?php

namespace App\Orchid\Screens\References;

use App\Models\Travel\TravelType;
use App\Orchid\Layouts\References\TravelTypeEditLayout;
use App\Orchid\Layouts\References\TravelTypeListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TravelTypeListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'list' => TravelType::filters([])->paginate(20)
        ];
    }

    public function name(): ?string
    {
        return 'Типы походов';
    }

    public function description(): ?string
    {
        return 'Справочник типов походов';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('travel_type')
                ->modalTitle('Create New Travel Type')
                ->method('saveTravelType')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            TravelTypeListLayout::class,
            Layout::modal('travel_type', TravelTypeEditLayout::class)->async('asyncGetTravelTypeList'),
        ];
    }

    public function asyncGetTravelTypeList(int $id = 0): array
    {
        return [
            'travel-type' => TravelType::loadBy($id) ?: new TravelType()
        ];
    }

    public function saveTravelType(Request $request): void
    {
        $data = $request->validate([
            'travel-type.name'        => 'required|string',
            'travel-type.description' => 'nullable|string',
        ])['travel-type'];

        TravelType::updateOrCreate(
            ['id' => (int)$request->get('id')],
            $data
        );

        Toast::info('Travel type was saved');
    }

    public function remove(int $id): void
    {
        TravelType::loadBy($id)?->delete();
    }
}
