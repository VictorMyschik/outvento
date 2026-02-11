<?php

declare(strict_types=1);

namespace App\Orchid\Screens\References;

use App\Models\ModelRole;
use App\Models\Notification\NotificationEventType;
use App\Orchid\Layouts\References\NotificationEventTypeEditLayout;
use App\Orchid\Layouts\References\NotificationEventTypeListLayout;
use App\Orchid\Rebuild\AttachmentHelper;
use App\Services\References\NotificationEventTypeService;
use Illuminate\Http\Request;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NotificationEventTypeScreen extends Screen
{
    public string $name = 'Типы оповещения';
    public string $description = 'Справочник типов оповещений для пользователей';

    public function __construct(private readonly NotificationEventTypeService $service) {}

    public function query(): iterable
    {
        return [
            'list' => NotificationEventType::filters([])->paginate(20)
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('reference')
                ->modalTitle('Create Notification Event Type')
                ->method('saveReferenceType')
                ->asyncParameters(['id' => 0])
        ];
    }

    public function layout(): iterable
    {
        return [
            NotificationEventTypeListLayout::class,
            Layout::modal('reference', NotificationEventTypeEditLayout::class)->async('asyncGetReferenceTypeList'),
        ];
    }

    public function asyncGetReferenceTypeList(int $id): array
    {
        $options = Role::pluck('name', 'id')->all();
        $roleValues = [];

        if ($id > 0) {
            $roleValues = Role::join(ModelRole::getTableName(), 'roles.id', '=', ModelRole::getTableName() . '.role_id')
                ->where(ModelRole::getTableName() . '.table_name', NotificationEventType::class)
                ->where(ModelRole::getTableName() . '.model_id', $id)
                ->pluck('name', 'roles.id')->all();
        }

        return [
            'roleOptions' => $options,
            'roleValues' => $roleValues,
            'reference' => NotificationEventType::loadBy($id)
        ];
    }

    public function saveReferenceType(Request $request, int $id): void
    {
        $data = $request->validate([
            'reference.category'    => 'required|string',
            'reference.code'        => 'required|string',
            'reference.title'       => 'required|string',
            'reference.description' => 'nullable|string',
            'reference.roles'       => 'required|array',
            'reference.roles.*'     => 'exists:roles,id',
        ])['reference'];

        $attachment = AttachmentHelper::getFile($request, 'reference');

        $this->service->saveNotificationType($id, $data, $attachment?->file);

        if ($attachment ?? null) {
            $attachment->delete();
        }
    }

    public function deleteImage(int $referenceTypeId): void
    {
        $this->service->deleteImage(NotificationEventType::loadByOrDie($referenceTypeId));
    }

    public function remove(int $id): void
    {
        try {
            (NotificationEventType::loadByOrDie($id))->delete();
        } catch (\Exception $exception) {
            Toast::info($exception->getMessage())->delay(1000);
        }
    }
}
