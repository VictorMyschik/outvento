<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Models\System\Settings;
use App\Orchid\Filters\System\SettingsFilter;
use App\Orchid\Layouts\System\Settings\SettingsEditLayout;
use App\Orchid\Layouts\System\Settings\SettingsListLayout;
use App\Services\System\Enum\SettingsKey;
use App\Services\System\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SettingsScreen extends Screen
{
    public function __construct(private readonly SettingsService $service) {}

    public string $name = 'Settings';

    public function description(): string
    {
        return 'Managing Site Settings';
    }

    public function query(): iterable
    {
        return [
            'list' => SettingsFilter::queryQuery(),
        ];
    }

    public function commandBar(): iterable
    {
        if (!empty($this->getSettingsOptions(null))) {
            return [
                ModalToggle::make('add')
                    ->class('mr-btn-success')
                    ->icon('plus')
                    ->modal('setup_modal')
                    ->modalTitle('Settings')
                    ->method('saveSettings')
                    ->asyncParameters(['id' => 0]),
            ];
        }

        return [
            Link::make('All settings are configured')
                ->class('mr-btn-default')
                ->icon('check'),
        ];
    }

    public function layout(): iterable
    {
        return [
            SettingsFilter::displayFilterCard(),
            SettingsListLayout::class,
            Layout::modal('setup_modal', SettingsEditLayout::class)->async('asyncGetSettings'),
        ];
    }

    #region Popup From
    public function asyncGetSettings(int $id = 0): iterable
    {
        $setup =  Settings::loadBy($id);

        return [
            'setup'   => $setup,
            'options' => $this->getSettingsOptions($setup?->getCodeKey()),
        ];
    }

    private function getSettingsOptions(?SettingsKey $excludeKey): array
    {
        $options = SettingsKey::getSelectList();

        foreach ($this->service->getList() as $key => $value) {
            if ($value->getCodeKey() === $excludeKey) {
                continue;
            }

            unset($options[$key]);
        }

        return $options;
    }

    public function saveSettings(Request $request): void
    {
        $id = (int)$request->get('id');
        $setupIn = (array)$request->get('setup');

        $this->service->saveSetting($id, $setupIn);

        Toast::success('Saved')->delay(1000);
    }

    public function remove(Request $request): void
    {
        $setup = Settings::loadByOrDie((int)$request->get('id'));
        $setup->delete();

        Toast::warning(__('Settings was removed'))->delay(1000);
    }
    #endregion

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [];
        foreach (SettingsFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('system.settings.list', $list);
    }

    public function clearFilter(): RedirectResponse
    {
        return redirect()->route('system.settings.list');
    }
    #endregion
}
