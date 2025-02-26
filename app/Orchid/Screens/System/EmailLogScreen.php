<?php

namespace App\Orchid\Screens\System;

use App\Models\Email\EmailLog;
use App\Orchid\Layouts\System\Emails\EmailLogListLayout;
use App\Orchid\Layouts\System\Emails\EmailLogViewLayout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class EmailLogScreen extends Screen
{
    public function name(): ?string
    {
        return 'Лог отправленных писем';
    }

    public function query(): iterable
    {
        return [
            'list' => EmailLog::filters([])->orderBy('id', 'DESC')->paginate(100),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Очистить лог')
                ->class('mr-border-radius-5')
                ->icon('trash')
                ->type(Color::WARNING())
                ->method('clear')
                ->confirm('Очистить лог'),
        ];
    }

    public function layout(): iterable
    {
        return [
            EmailLogListLayout::class,

            Layout::modal('view_body', EmailLogViewLayout::class)->withoutApplyButton()->async('asyncGetEmail')->size(Modal::SIZE_LG)
        ];
    }

    public function asyncGetEmail(int $id): array
    {
        $emailLog = EmailLog::find($id);

        return [
            'email'   => $emailLog->email,
            'subject' => $emailLog->subject,
            'body'    => $emailLog->getBody(),
        ];
    }

    public function clear(): void
    {
        EmailLog::truncate();
    }

    public function deleteRow(int $id): void
    {
        EmailLog::find($id)->delete();
    }
}