<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Services\System\Cache\CacheRedisClass;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class CacheScreen extends Screen
{
    public ?string $name = 'Cache System';

    public function __construct(private readonly CacheRedisClass $cacheInfoClass) {}

    public function query(): iterable
    {
        return [
            'cache' => null,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Clear')
                ->class('mr-btn-danger')
                ->icon('trash')
                ->novalidate()
                ->method('cacheClear'),
        ];
    }

    public function layout(): iterable
    {
        $cacheInfoDTO = $this->cacheInfoClass->getDisplayInfo();

        return [
            Layout::view('admin.system.cache', ['cacheInfoDTO' => $cacheInfoDTO]),
        ];
    }

    public function cacheClear(): RedirectResponse
    {
        return redirect()->route('clear');
    }
}
