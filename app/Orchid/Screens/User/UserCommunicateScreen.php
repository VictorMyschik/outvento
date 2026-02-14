<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\User\Request\CommunicationRequest;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Orchid\Filters\UserCommunicationFilter;
use App\Orchid\Layouts\User\UserCommunicateEditLayout;
use App\Orchid\Layouts\User\UserCommunicateListLayout;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserCommunicateScreen extends Screen
{
    public string $name = 'Контакты пользователей';

    public function __construct(private readonly UserService $service) {}

    public function query(): iterable
    {
        return [
            'list' => UserCommunicationFilter::runQuery()->paginate(20),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить контакт')
                ->class('mr-btn-success')
                ->modal('communicate_modal')
                ->method('saveCommunication')
                ->modalTitle('Добавить контакт')
                ->asyncParameters(['id' => 0])
                ->icon('plus'),
        ];
    }

    public function layout(): iterable
    {
        return [
            UserCommunicationFilter::displayFilterCard(),
            UserCommunicateListLayout::class,
            Layout::modal('communicate_modal', UserCommunicateEditLayout::class)->async('asyncGetCommunicate'),
        ];
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserCommunicationFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('profiles.communication.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.communication.list');
    }

    public function asyncGetCommunicate(int $id = 0): array
    {
        return Communication::loadBy($id)?->getAttributes() ?: [];
    }

    public function saveCommunication(CommunicationRequest $request, int $id): void
    {
        $data = $request->getUpdateData();
        $data['user_id'] = $request->get('user_id');

        $this->service->saveCommunication($id, $data);

        Toast::info('Контакт сохранен');
    }

    public function removeCommunication(int $userId, int $id): void
    {
        $this->service->deleteCommunication(User::findOrFail($userId)->id, $id);
    }

    public function remove(int $id): void
    {
        $communicate = Communication::loadByOrDie($id);
        $communicate->delete();

        Toast::info('Контакт удален');
    }
}
