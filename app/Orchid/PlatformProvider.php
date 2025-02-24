<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    public function menu(): array
    {
        return [
            Menu::make('System')->icon('settings')->list([
                Menu::make('Cron')->route('system.info.cron'),
                Menu::make('Cache')->route('system.cache'),
                Menu::make('Settings')->route('system.settings.list'),
                Menu::make('Failed jobs')->route('system.failed.jobs'),
                Menu::make('API documentation')->target('_blank')->href('/api/documentation'),
            ])->divider(),

            // References
            Menu::make('References')->icon('grid')->list([
                Menu::make('Travel types')->icon('bs.list')->route('reference.travel-type.list'),
                Menu::make('Emails')->icon('bs.list')->route('reference.email.list'),
                Menu::make('Category Equipments')->icon('bs.list')->route('reference.category.equipments.list'),
                Menu::make('Equipments')->icon('bs.list')->route('reference.equipments.list'),
            ]),

            // FAQ
            Menu::make('FAQ')->title('Information')->icon('bs.book')->route('faq.list'),

            Menu::make('Language')->icon('language')->route('language.list'),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),
        ];
    }

    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
