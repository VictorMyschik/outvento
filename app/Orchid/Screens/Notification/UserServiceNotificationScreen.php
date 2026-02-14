<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Notification;

use App\Models\Notification\ServiceNotification;
use App\Orchid\Filters\MessageLog\UserServiceNotificationFilter;
use App\Orchid\Layouts\Notifications\UserNotificationSettingEditLayout;
use App\Orchid\Layouts\Notifications\UserNotificationSettingsListLayout;
use App\Services\Notifications\ServiceNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

final class UserServiceNotificationScreen extends Screen
{
    public function __construct(
        private readonly Request                    $request,
        private readonly ServiceNotificationService $service,
    ) {}

    public string $name = 'User Service Notification';

    public function query(): iterable
    {
        return [
            'list' => UserServiceNotificationFilter::runQuery()->paginate(10),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('user_notification_modal')
                ->modalTitle('Create user service notification')
                ->method('saveUserSetting')
                ->asyncParameters(['id' => 0]),
        ];
    }

    public function layout(): iterable
    {
        return [
            UserServiceNotificationFilter::displayFilterCard($this->request),
            UserNotificationSettingsListLayout::class,

            Layout::modal('user_notification_modal', UserNotificationSettingEditLayout::class)->async('asyncGetUserSetting'),
        ];
    }

    public function asyncGetUserSetting(int $id): array
    {
        return [
            'setting' => ServiceNotification::loadBy($id),
        ];
    }

    public function saveUserSetting(Request $request, int $id): void
    {
        $input = Validator::make($request->all(), [
            'setting.user_id'          => 'required|integer',
            'setting.event'            => 'required|integer',
            'setting.communication_id' => 'required|integer'
        ])->validate()['setting'];

        $this->service->saveUserSetting($id, $input);
    }

    public function deleteRow(int $id): void
    {
        $this->service->deleteUserSetting($id);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $input = $request->all(UserServiceNotificationFilter::FIELDS);

        $list = [];
        foreach (UserServiceNotificationFilter::FIELDS as $item) {
            if (!is_null($input[$item])) {
                $list[$item] = $input[$item];
            }
        }

        return redirect()->route('user.service.notification.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('user.service.notification.list');
    }

    #endregion

    public function __destruct() {}
}
