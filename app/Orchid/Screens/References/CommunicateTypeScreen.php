<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\UserInfo\CommunicationType;
use App\Orchid\Layouts\References\BaseReferenceListLayout;
use App\Orchid\Layouts\References\ReferenceBaseTypeEditLayout;
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
                ->modal('reference')
                ->modalTitle('Create New Reference Type')
                ->method('saveReferenceType')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            BaseReferenceListLayout::class,
            Layout::modal('reference', ReferenceBaseTypeEditLayout::class)->async('asyncGetReferenceTypeList'),
        ];
    }

    public function asyncGetReferenceTypeList(int $id): array
    {
        return [
            'reference' => CommunicationType::loadBy($id)
        ];
    }

    public function saveReferenceType(Request $request, int $id): void
    {
        $data = $request->validate([
            'reference.name_ru' => 'required|string',
            'reference.name_en' => 'required|string',
            'reference.name_pl' => 'required|string',
        ])['reference'];

        $attachment = AttachmentHelper::getFile($request, 'reference');

        $this->service->saveCommunicationType($id, $data, $attachment?->file);

        if ($attachment ?? null) {
            $attachment->delete();
        }
    }

    public function deleteImage(int $referenceTypeId): void
    {
        $this->service->deleteImage(CommunicationType::loadByOrDie($referenceTypeId));
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
