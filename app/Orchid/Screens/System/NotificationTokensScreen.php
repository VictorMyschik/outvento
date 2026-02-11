<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Models\NotificationToken;
use App\Orchid\Filters\System\NotificationTokenFilter;
use App\Orchid\Layouts\Lego\InfoRawModalLayout;
use App\Orchid\Layouts\System\NotificationTokenListLayout;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NotificationTokensScreen extends Screen
{
    protected ?string $name = 'Notification Tokens';
    protected ?string $description = 'Manage notification tokens used for sending notifications to users';

    public function __construct(
        private readonly Request             $request,
        private readonly NotificationService $service,
    ) {}

    public function query(): iterable
    {
        return [
            'list' => NotificationTokenFilter::runQuery()->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Clear')
                ->class('mr-btn-danger')
                ->icon('trash')
                ->method('purge')
                ->confirm('Очистить все токены уведомлений?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            NotificationTokenFilter::displayFilterCard($this->request),
            NotificationTokenListLayout::class,
            Layout::modal('details_modal', InfoRawModalLayout::class)->async('asyncGetNotificationToken')->size(Modal::SIZE_LG),
        ];
    }

    public function asyncGetNotificationToken(int $id = 0): array
    {
        $notification = NotificationToken::loadByOrDie($id);
        if (!$notification) {
            return ['view' => null];
        }

        return ['view' => $this->service->buildView($notification)->render()];
    }

    public function purge(): void
    {
        NotificationToken::truncate();
    }

    public function remove(int $id): void
    {
        NotificationToken::loadBy($id)->delete();
    }

    public function resendNotificationToken(int $id): void
    {
        try {
            $notificationToken = NotificationToken::loadByOrDie($id);
            $this->service->send($notificationToken);
            $notificationToken->touch();
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
        foreach (NotificationTokenFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('system.notification.tokens', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('system.notification.tokens');
    }
    #endregion
}
