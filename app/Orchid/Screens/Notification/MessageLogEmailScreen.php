<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Notification;

use App\Models\MessageLog\EmailLog;
use App\Orchid\Filters\MessageLog\MessageLogEmailFilter;
use App\Orchid\Layouts\Lego\RawLogViewLayout;
use App\Orchid\Layouts\Notifications\EmailLogListLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

final class MessageLogEmailScreen extends Screen
{
    public function __construct(
        private readonly Request                   $request,
    ) {}

    public string $name = 'Лог отправленных писем';

    public function query(): iterable
    {
        return [
            'list' => MessageLogEmailFilter::runQuery()->paginate(20),
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
            MessageLogEmailFilter::displayFilterCard($this->request),
            EmailLogListLayout::class,

            Layout::modal('view_body', RawLogViewLayout::class)->withoutApplyButton()->async('asyncGetEmail')->size(Modal::SIZE_LG)
        ];
    }

    public function asyncGetEmail(int $id): array
    {
        $emailLog = EmailLog::find($id);

        return [
            'email'   => $emailLog->email,
            'subject' => $emailLog->subject,
            'body'    => $emailLog->sl,
        ];
    }

    public function clear(): void
    {
        EmailLog::truncate();
    }

    public function deleteRow(int $id): void
    {
        EmailLog::where('id', $id)->delete();
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $input = $request->all(MessageLogEmailFilter::FIELDS);

        $list = [];
        foreach (MessageLogEmailFilter::FIELDS as $item) {
            if (!is_null($input[$item])) {
                $list[$item] = $input[$item];
            }
        }

        return redirect()->route('system.email.log', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('system.email.log');
    }
    #endregion
}