<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Email;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class EmailScreen extends Screen
{
    public function query(): iterable
    {
        return [];
    }

    public function name(): ?string
    {
        return 'Email';
    }

    public function description(): ?string
    {
        return 'Шаблоны писем';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $fakeData['travel_invite'] = [
            'token'       => '8cde1582275a2afe5dd535f7e31108f1',
            'name'        => 'Восхождение на Казбек',
            'travel_type' => 'Горный поход',
        ];

        return [
            Layout::tabs([
                'Travel Invite' => Layout::view('emails.travel_invite', ['data' => $fakeData['travel_invite']]),
                //'Email'       => Layout::view('emails.travel_invite', ['data' => $fakeData['travel_invite']]),
            ]),
        ];
    }
}
