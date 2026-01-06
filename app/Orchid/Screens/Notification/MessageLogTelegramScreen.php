<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Notification;

use App\Mail\RawEmail;
use App\Models\MessageLog\EmailLog;
use App\Models\MessageLog\TelegramLog;
use App\Orchid\Filters\MessageLog\MessageLogTelegramFilter;
use App\Orchid\Layouts\Lego\RawLogViewLayout;
use App\Orchid\Layouts\Notifications\TelegramLogListLayout;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

final class MessageLogTelegramScreen extends Screen
{
    public string $name = 'Лог отправленных сообщений';
    public function __construct(
        private readonly Request         $request,
        private readonly TelegramService $telegramService,
    ) {}
    public function query(): iterable
    {
        return [
            'list' => MessageLogTelegramFilter::runQuery()->paginate(50),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Очистить лог')
                ->class('mr-btn-danger')
                ->icon('trash')
                ->method('clear')
                ->confirm('Очистить лог?'),
        ];
    }

    public function layout(): iterable
    {
        return [
            MessageLogTelegramFilter::displayFilterCard($this->request),
            TelegramLogListLayout::class,

            Layout::modal('view_body', RawLogViewLayout::class)->withoutApplyButton()->async('asyncGetLog')->size(Modal::SIZE_LG)
        ];
    }
    public function resendTelegramMessage(int $id): void
    {
        $log = EmailLog::loadByOrDie($id);

        $this->emailService->send($log->getEmail(), new RawEmail($log->getSubject(), $log->getBody()), $log->getType(), false);
    }

    public function asyncGetLog(int $id): array
    {
        $log = TelegramLog::loadByOrDie($id);

        return [
            'user_tg' => $log->user_tg,
            'body'    => $log->message,
        ];
    }

    public function clear(): void
    {
        TelegramLog::truncate();
    }

    public function deleteRow(int $id): void
    {
        TelegramLog::where('id', $id)->delete();
    }
}