<?php

namespace App\Orchid\Screens\References;

use App\Models\Travel\TravelType;
use App\Orchid\Layouts\References\TravelTypeEditLayout;
use App\Orchid\Layouts\References\TravelTypeListLayout;
use App\Orchid\Rebuild\AttachmentHelper;
use App\Services\References\ReferenceService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TravelTypeListScreen extends Screen
{
    public function __construct(private ReferenceService $service) {}

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
            'travel-type' => TravelType::loadBy($id)
        ];
    }

    public function saveTravelType(Request $request, int $id): void
    {
        $data = $request->validate([
            'travel-type.name_ru' => 'required|string',
            'travel-type.name_en' => 'required|string',
            'travel-type.name_pl' => 'required|string',
        ])['travel-type'];

        $attachment = AttachmentHelper::getFile($request, 'travel-type');

        $this->service->saveTravelType($id, $data, $attachment?->file);

        if ($attachment ?? null) {
            $attachment->delete();
        }
    }

    public function deleteImage(int $travelTypeId): void
    {
        $this->service->deleteImage(TravelType::loadByOrDie($travelTypeId));
    }

    public function remove(int $id): void
    {
        try {
            (TravelType::loadByOrDie($id))->delete();
        } catch (\Exception $exception) {
            Toast::info($exception->getMessage())->delay(1000);
        }
    }
}
