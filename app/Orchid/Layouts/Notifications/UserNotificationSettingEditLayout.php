<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Notifications;

use App\Models\User;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\NotificationType;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;

class UserNotificationSettingEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Group::make([
                Switcher::make('setting.active')->sendTrueOrFalse()->title('Active'),

                Relation::make('setting.user_id')
                    ->fromModel(User::class, 'name', 'id')
                    ->required()
                    ->title('Пользователь'),
            ]),
            ViewField::make('separator')->view('space'),
            Group::make([
                Select::make('setting.notification_key')
                    ->options(NotificationType::getSelectList())
                    ->required()
                    ->title('Тип оповещения'),

                Select::make('setting.channel')
                    ->options(NotificationChannel::getSelectList())
                    ->required()
                    ->title('Канал'),
            ]),
        ];
    }
}
