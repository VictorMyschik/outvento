<?php

namespace App\Orchid\Screens\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PurgeScreen extends Screen
{

    public function name(): ?string
    {
        return 'Полная очистка базы данных';
    }

    public function description(): ?string
    {
        return 'Будет пересоздана вся база данных (только схема "public"), все данные будут удалены. Восстановить данные будет невозможно';
    }

    public function query(): iterable
    {
        return [];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $btn = Label::make('')->value('Доступно только в тестовой среде!');

        if (ENV('PRODUCTION') !== true) {
            $btn = Button::make('Purge')
                ->icon('trash')
                ->method('purge')
                ->confirm('Вы уверены, что хотите удалить все данные?');
        }

        return [
            Layout::rows([$btn])
        ];
    }

    public function purge(Request $request): void
    {
        Artisan::call('purge');
        Toast::warning('Purge is done')->delay(1000);
    }
}
