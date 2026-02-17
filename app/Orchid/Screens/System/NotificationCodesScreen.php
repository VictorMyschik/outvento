<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Models\NotificationCode;
use App\Models\NotificationToken;
use App\Orchid\Filters\System\NotificationCodeFilter;
use App\Orchid\Layouts\Lego\InfoRawModalLayout;
use App\Orchid\Layouts\System\NotificationCodeListLayout;
use App\Services\Notifications\SystemNotificationService;
use App\Services\User\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NotificationCodesScreen extends Screen
{
    protected ?string $name = 'Notification Codes';
    protected ?string $description = 'Manage notification codes used for sending notifications to users';

    public function __construct(
        private readonly Request                   $request,
        private readonly SystemNotificationService $service,
        private readonly AuthService               $authService,
    ) {}

    public function query(): iterable
    {
        return [
            'list' => NotificationCodeFilter::runQuery()->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Clear')
                ->class('mr-btn-danger')
                ->icon('trash')
                ->method('purge')
                ->confirm('Очистить все коды?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            NotificationCodeFilter::displayFilterCard($this->request),
            NotificationCodeListLayout::class,
            Layout::modal('details_modal', InfoRawModalLayout::class)->async('asyncGetNotificationCode')->size(Modal::SIZE_LG),
        ];
    }

    public function asyncGetNotificationCode(int $id = 0): array
    {
        $notification = NotificationToken::loadByOrDie($id);
        if (!$notification) {
            return ['view' => null];
        }

        return ['view' => $this->service->buildView($notification)->render()];
    }

    public function purge(): void
    {
        NotificationCode::truncate();
    }

    public function remove(int $id): void
    {
        NotificationCode::loadBy($id)->delete();
    }

    public function resendNotificationCode(int $id): void
    {
        try {
            $notificationCode = NotificationCode::loadByOrDie($id);
            $this->authService->sendVerifyNotification($notificationCode->getUser());
            $notificationCode->touch();
            Toast::info('Notification resent successfully.');
        } catch (\Throwable $e) {
            Toast::error('Error resending notification: ' . $e->getMessage());
            return;
        }
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (NotificationCodeFilter::FIELDS as $item) {
            if (!is_null($request->input($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('system.notification.codes', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('system.notification.codes');
    }
    #endregion
}
