<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\UserInfo\CommunicationType;
use App\Orchid\Layouts\References\CommunicationTypeEditLayout;
use App\Orchid\Layouts\References\CommunicationTypeListLayout;
use App\Orchid\Rebuild\AttachmentHelper;
use App\Services\References\ReferenceService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CommunicateTypeScreen extends Screen
{
    public string $name = 'Тип коммуникации';
    public string $description = 'Справочник типов коммуникации пользователей';

    public function __construct(private readonly ReferenceService $service) {}

    public function query(): iterable
    {
        return [
            'list' => CommunicationType::filters([])->paginate(20)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('communication_type')
                ->modalTitle('Create New Communication Type')
                ->method('saveCommunicationType')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            CommunicationTypeListLayout::class,
            Layout::modal('communication_type', CommunicationTypeEditLayout::class)->async('asyncGetCommunicationTypeList'),
        ];
    }

    public function asyncGetCommunicationTypeList(int $id): array
    {
        return [
            'communication-type' => CommunicationType::loadBy($id)
        ];
    }

    public function saveCommunicationType(Request $request, int $id): void
    {
        $data = $request->validate([
            'communication-type.name_ru' => 'required|string',
            'communication-type.name_en' => 'required|string',
            'communication-type.name_pl' => 'required|string',
        ])['communication-type'];

        $attachment = AttachmentHelper::getFile($request, 'communication-type');

        $this->service->saveCommunicationType($id, $data, $attachment?->file);

        if ($attachment ?? null) {
            $attachment->delete();
        }
    }

    public function deleteImage(int $communicationTypeId): void
    {
        $this->service->deleteImage(CommunicationType::loadByOrDie($communicationTypeId));
    }

    public function remove(int $id): void
    {
        try {
            (CommunicationType::loadByOrDie($id))->delete();
        } catch (\Exception $exception) {
            Toast::info($exception->getMessage())->delay(1000);
        }
    }
}
