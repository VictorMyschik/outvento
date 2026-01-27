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
                Menu::make('Communication Type')->icon('bs.list')->route('reference.communication-type.list'),
            ]),
            Menu::make('Notification')->icon('grid')->list([
                Menu::make('Email Subscriptions')->icon('bs.send')->route('subscriptions.list'),
                Menu::make('User Settings')->icon('bs.list')->route('notification.user.settings.list'),
            ])->divider(),

            Menu::make('User List')->icon('grid')->list([
                Menu::make('Users list')->icon('bs.people')->route('profiles.list'),
                Menu::make('Communication')->icon('bs.person-lines-fill')->route('profiles.communication.list'),
            ])->divider(),
            // Menu::make('Wishlists')->icon('bs.list')->route('wishlist.list')->divider(),

            // Catalog
            Menu::make('Catalog')->icon('grid')->list([
                Menu::make('Товары')->icon('list')->route('goods.list'),
                Menu::make('Группы товаров')->icon('list')->route('type.list'),
                Menu::make('Производители')->icon('list')->route('manufacturer.list')->divider(),
            ]),

            Menu::make('Language')->icon('language')->route('language.translate.list'),

            Menu::make('System')->icon('settings')->list([
                Menu::make('Logs')->href('/log-viewer')->target('_blank'),
                Menu::make('Cron')->route('system.info.cron'),
                Menu::make('Cache')->route('system.cache'),
                Menu::make('Settings')->route('system.settings.list'),
                // Menu::make('Jobs')->route('system.jobs'),
                Menu::make('Failed jobs')->route('system.failed.jobs'),
                Menu::make('Database')->route('system.database'),
                Menu::make('PHP Info')->route('system.phpinfo'),
                Menu::make('Supervisor')->route('system.config'),
                Menu::make('API documentation')->target('_blank')->href('/api/docs'),
            ])->divider(),

            Menu::make('Other')->icon('list')->list([
                Menu::make('Newsletter')->icon('bs.list')->route('newsletter.news.list'),
                Menu::make('FAQ')->icon('bs.book')->route('faq.list'),
                Menu::make('Terms & Conditions')->icon('bs.file-earmark-text')->route('other.terms.and.conditions'),
            ])->divider(),

            Menu::make('Access Controls')->icon('grid')->list([
                Menu::make(__('Users'))
                    ->icon('bs.people')
                    ->route('platform.systems.users')
                    ->permission('platform.systems.users'),

                Menu::make(__('Roles'))
                    ->icon('bs.shield')
                    ->route('platform.systems.roles')
                    ->permission('platform.systems.roles'),
            ])->divider(),
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
