<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Http\Controllers\API\User\Request\CommunicationRequest;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Orchid\Filters\User\UserNotificationFilter;
use App\Orchid\Filters\UserCommunicationFilter;
use App\Orchid\Layouts\User\UserNotificationEditLayout;
use App\Orchid\Layouts\User\UserNotificationListLayout;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserNotificationListScreen extends Screen
{
    public string $name = 'User Notifications';

    public function __construct(private readonly UserService $service) {}

    public function query(): iterable
    {
        return [
            'list' => UserNotificationFilter::runQuery()->paginate(20),
        ];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            UserNotificationFilter::displayFilterCard(request()),
            UserNotificationListLayout::class,
            Layout::modal('user_notification_modal', UserNotificationShowLayout::class)->async('asyncGetNotification'),
        ];
    }

    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (UserCommunicationFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('profiles.communication.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('profiles.communication.list');
    }

    public function asyncGetNotification(int $id = 0): array
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
