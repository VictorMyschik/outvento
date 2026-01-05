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
            // Travel
            Menu::make('Travel list')->icon('bs.list')->route('travel.list'),

            // References
            Menu::make('References')->icon('grid')->list([
                Menu::make('Travel types')->icon('bs.list')->route('reference.travel-type.list'),
                Menu::make('Emails')->icon('bs.list')->route('reference.email.list'),
                Menu::make('Category Equipments')->icon('bs.list')->route('reference.category.equipments.list'),
                Menu::make('Equipments')->icon('bs.list')->route('reference.equipments.list'),
                Menu::make('Countries')->icon('bs.list')->route('reference.countries.list'),
                Menu::make('Cities')->icon('bs.list')->route('reference.cities.list'),
            ]),

            Menu::make('Subscriptions')->icon('bs.send')->route('subscriptions.list')->divider(),

            Menu::make('Newsletter')->icon('bs.list')->route('newsletter.news.list')->divider(),

            Menu::make('Notification')->icon('grid')->list([
                Menu::make('User Settings')->icon('bs.list')->route('notification.user.settings.list'),
                Menu::make('Email')->icon('bs.list')->route('notification.log.email.list'),
                Menu::make('Telegram')->icon('bs.list')->route('notification.log.telegram.list'),
            ])->divider(),

            Menu::make('Wishlists')->icon('bs.list')->route('wishlist.list')->divider(),

            // Catalog
            Menu::make('Catalog')->icon('grid')->list([
                Menu::make('Товары')->icon('list')->route('goods.list'),
                Menu::make('Группы товаров')->icon('list')->route('type.list'),
                Menu::make('Производители')->icon('list')->route('manufacturer.list')->divider(),
            ]),

            // FAQ
            Menu::make('FAQ')->icon('bs.book')->route('faq.list'),

            Menu::make('Language')->icon('language')->route('language.translate.list'),

            Menu::make('System')->icon('settings')->list([
                Menu::make('Email log')->route('system.email.log'),
                Menu::make('Cron')->route('system.info.cron'),
                Menu::make('Cache')->route('system.cache'),
                Menu::make('Settings')->route('system.settings.list'),
                // Menu::make('Jobs')->route('system.jobs'),
                Menu::make('Failed jobs')->route('system.failed.jobs'),
                Menu::make('Database')->route('system.database'),
                Menu::make('Supervisor')->route('system.supervisor'),
                Menu::make('API documentation')->target('_blank')->href('/api/docs'),
            ])->divider(),

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
