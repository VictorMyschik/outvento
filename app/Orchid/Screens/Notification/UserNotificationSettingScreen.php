<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Notification;

use App\Orchid\Filters\MessageLog\UserNotificationSettingFilter;
use App\Orchid\Layouts\Notifications\UserNotificationSettingEditLayout;
use App\Orchid\Layouts\Notifications\UserNotificationSettingsListLayout;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

final class UserNotificationSettingScreen extends Screen
{
    public function __construct(
        private readonly Request             $request,
        private readonly NotificationService $service,
    ) {}

    public string $name = 'Настройки оповещения';

    public function query(): iterable
    {
        return [
            'list' => UserNotificationSettingFilter::runQuery()->paginate(20),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->class('mr-btn-success')
                ->icon('plus')
                ->modal('user_notification_modal')
                ->modalTitle('Create New User Setting')
                ->method('saveUserSetting')
                ->asyncParameters(['id' => 0]),
        ];
    }

    public function layout(): iterable
    {
        return [
            UserNotificationSettingFilter::displayFilterCard($this->request),
            UserNotificationSettingsListLayout::class,

            Layout::modal('user_notification_modal', UserNotificationSettingEditLayout::class)->async('asyncGetUserSetting'),
        ];
    }

    public function asyncGetUserSetting(int $id): array
    {
        return [
            'setting' => $this->service->getUserNotificationSettingById($id),
        ];
    }

    public function saveUserSetting(Request $request, int $id): void
    {
        $input = Validator::make($request->all(), [
            'setting.active'           => 'nullable',
            'setting.user_id'          => 'required|integer',
            'setting.notification_key' => 'required',
            'setting.channel'          => 'required'
        ])->validate()['setting'];
        $input['active'] = (bool)$input['active'] ?? false;

        $this->service->saveUserSetting($id, $input);
    }

    public function deleteRow(int $id): void
    {
        $this->service->deleteUserSetting($id);
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $input = $request->all(UserNotificationSettingFilter::FIELDS);

        $list = [];
        foreach (UserNotificationSettingFilter::FIELDS as $item) {
            if (!is_null($input[$item])) {
                $list[$item] = $input[$item];
            }
        }

        return redirect()->route('notification.user.settings.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('notification.user.settings.list');
    }

    #endregion

    public function __destruct() {}
}