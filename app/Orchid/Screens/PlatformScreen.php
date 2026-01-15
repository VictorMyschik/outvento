<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Services\System\Enum\SettingsKey;
use App\Services\System\SettingsService;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    public function __construct(private readonly SettingsService $service) {}

    public function query(): iterable
    {
        return [];
    }

    public string $name = 'Get Started';

    public string $description = 'Welcome to our platform.';

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::split([
                Layout::rows([
                    Link::make('Telegram channel link')->target('_blank')->href($this->service->getByKey(SettingsKey::TelegramChannel)->getValue()),
                    ViewField::make('')->view('qr-code')->value($this->service->getByKey(SettingsKey::TelegramChannel)->getValue()),
                ])
            ])
        ];
    }
}
