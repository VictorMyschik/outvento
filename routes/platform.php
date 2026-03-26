<?php

declare(strict_types=1);

use App\Orchid\Screens\Catalog\CatalogAttributeScreen;
use App\Orchid\Screens\Catalog\CatalogGoodDetailsScreen;
use App\Orchid\Screens\Catalog\CatalogGoodsScreen;
use App\Orchid\Screens\Catalog\CatalogGroupsScreen;
use App\Orchid\Screens\Catalog\ManufacturerScreen;
use App\Orchid\Screens\FAQScreen;
use App\Orchid\Screens\Forms\FormsScreen;
use App\Orchid\Screens\Language\TranslateScreen;
use App\Orchid\Screens\Newsletter\NewsEditScreen;
use App\Orchid\Screens\Newsletter\NewsletterScreen;
use App\Orchid\Screens\Notification\MessageLogEmailScreen;
use App\Orchid\Screens\Notification\UserServiceNotificationScreen;
use App\Orchid\Screens\Other\LegalDocumentEditScreen;
use App\Orchid\Screens\Other\LegalDocumentsScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Promo\SubscriptionScreen;
use App\Orchid\Screens\References\CategoryEquipmentScreen;
use App\Orchid\Screens\References\CitiesScreen;
use App\Orchid\Screens\References\CountryScreen;
use App\Orchid\Screens\References\EmailScreen;
use App\Orchid\Screens\References\EquipmentScreen;
use App\Orchid\Screens\References\UserLanguageScreen;
use App\Orchid\Screens\References\UserLocationScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\System\CacheScreen;
use App\Orchid\Screens\System\CronScreen;
use App\Orchid\Screens\System\DatabaseScreen;
use App\Orchid\Screens\System\DatabaseTableScreen;
use App\Orchid\Screens\System\FailedJobsScreen;
use App\Orchid\Screens\System\JobsScreen;
use App\Orchid\Screens\System\NotificationCodesScreen;
use App\Orchid\Screens\System\NotificationTokensScreen;
use App\Orchid\Screens\System\PhpInfoScreen;
use App\Orchid\Screens\System\PurgeScreen;
use App\Orchid\Screens\System\SettingsScreen;
use App\Orchid\Screens\System\SupervisorScreen;
use App\Orchid\Screens\Travel\TravelListScreen;
use App\Orchid\Screens\User\Conversations\UserConversationDetailsScreen;
use App\Orchid\Screens\User\Conversations\UserConversationsListScreen;
use App\Orchid\Screens\User\Conversations\UserGroupConversationDetailsScreen;
use App\Orchid\Screens\User\Conversations\UserGroupConversationsListScreen;
use App\Orchid\Screens\User\ProfileScreen;
use App\Orchid\Screens\User\UserCommunicateScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use App\Orchid\Screens\User\UserTravelDetailsScreen;
use App\Orchid\Screens\User\UserTravelListScreen;
use App\Orchid\Screens\Wishlist\WishlistScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn(Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn(Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// System
Route::screen('system/email/log/list', MessageLogEmailScreen::class)->name('system.email.log');
Route::screen('system/settings/list', SettingsScreen::class)->name('system.settings.list');
Route::screen('system/cache', CacheScreen::class)->name('system.cache');
Route::screen('system/cron', CronScreen::class)->name('system.info.cron');
Route::screen('system/purge', PurgeScreen::class)->name('system.purge');
Route::screen('system/jobs', JobsScreen::class)->name('system.jobs');
Route::screen('system/failed-jobs', FailedJobsScreen::class)->name('system.failed.jobs');
Route::screen('system/database', DatabaseScreen::class)->name('system.database');
Route::screen('system/database/table/{table}', DatabaseTableScreen::class)->name('system.database.table');
Route::screen('system/config', SupervisorScreen::class)->name('system.config');
Route::screen('system/phpinfo', PhpInfoScreen::class)->name('system.phpinfo');
Route::screen('system/notification-tokens', NotificationTokensScreen::class)->name('system.notification.tokens');
Route::screen('system/notification-codes', NotificationCodesScreen::class)->name('system.notification.codes');

// Language
Route::screen('language/translate', TranslateScreen::class)->name('language.translate.list');

// FAQ
Route::screen('/faq/list', FAQScreen::class)->name('faq.list');
//// News
Route::screen('newsletter/list', NewsletterScreen::class)->name('newsletter.news.list');
Route::screen('newsletter/{news_id}/edit', NewsEditScreen::class)->name('newsletter.news.edit');

/// Promo
Route::screen('promo/subscriptions/list', SubscriptionScreen::class)->name('promo.subscriptions.list');

// Users
Route::screen('/profiles/list', UserProfileListScreen::class)->name('profiles.list');
Route::screen('/profiles/{user}/details', ProfileScreen::class)->name('profiles.details');
Route::screen('/profiles/{user}/travels', UserTravelListScreen::class)->name('profiles.travels');
Route::screen('/profiles/{user}/conversations', UserConversationsListScreen::class)->name('profiles.conversations.list');
Route::screen('/profiles/{user}/group-conversations', UserGroupConversationsListScreen::class)->name('profiles.group-conversations.list');
Route::screen('/profiles/{user}/conversation/{conversation}/details', UserConversationDetailsScreen::class)->name('profiles.messages');
Route::screen('/profiles/{user}/group-conversation/{conversation}/details', UserGroupConversationDetailsScreen::class)->name('profiles.group-messages');
Route::screen('/profiles/{user}/travel/{travel}/details', UserTravelDetailsScreen::class)->name('profiles.travel.details');
Route::screen('/profiles/communications/list', UserCommunicateScreen::class)->name('profiles.communication.list');

// Travel
Route::screen('/travel/list', TravelListScreen::class)->name('travel.list');

// References
Route::screen('/reference/category-equipments/list', CategoryEquipmentScreen::class)->name('reference.category.equipments.list');
Route::screen('/reference/equipments/list', EquipmentScreen::class)->name('reference.equipments.list');
Route::screen('/reference/cities/list', CitiesScreen::class)->name('reference.cities.list');
Route::screen('/reference/user/location', UserLocationScreen::class)->name('reference.user.location');
Route::screen('/reference/countries/list', CountryScreen::class)->name('reference.countries.list');
Route::screen('/reference/emails', EmailScreen::class)->name('reference.email.list');
Route::screen('/reference/user/languages', UserLanguageScreen::class)->name('reference.user.languages');

// Forms
Route::screen('/forms/list', FormsScreen::class)->name('forms.list');

/// Message Log
Route::screen('/user/service/notification', UserServiceNotificationScreen::class)->name('user.service.notification.list');
//Route::screen('/notification/log/email/list', MessageLogEmailScreen::class)->name('notification.log.email.list');
//Route::screen('/notification/log/telegram/list', MessageLogTelegramScreen::class)->name('notification.log.telegram.list');

// Wish List
Route::screen('/wishlist/list', WishlistScreen::class)->name('wishlist.list');

// Catalog
Route::screen('/catalog/goods/list', CatalogGoodsScreen::class)->name('goods.list');
Route::screen('/catalog/good/{id}/details', CatalogGoodDetailsScreen::class)->name('goods.details');
Route::screen('/catalog/manufacturers/list', ManufacturerScreen::class)->name('manufacturer.list');
Route::screen('/catalog/types/list', CatalogGroupsScreen::class)->name('type.list');
Route::screen('/catalog/groups/list', CatalogGroupsScreen::class)->name('catalog.groups.list');
Route::screen('/catalog/group/{group_id}/attributes/list', CatalogAttributeScreen::class)->name('catalog.group.attributes');
/// Other routes...
Route::screen('/legal.documents/list', LegalDocumentsScreen::class)->name('legal.documents.list');
Route::screen('/legal.documents/{id}/details', LegalDocumentEditScreen::class)->name('legal.documents.edit');
