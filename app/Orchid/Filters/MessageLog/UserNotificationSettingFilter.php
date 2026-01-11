<?php

declare(strict_types=1);

namespace App\Orchid\Filters\MessageLog;

use App\Models\Notification\UserNotificationSetting;
use App\Models\User;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\NotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserNotificationSettingFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'userId',
        'notificationKey',
        'channelType',
        'active',
        'token',
        'createdAt',
        'updatedAt',
    ];

    public static function runQuery(): Builder
    {
        return UserNotificationSetting::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['notificationKey'])) {
            $builder->where('notification_key', (string)$input['notificationKey']);
        }

        if (!empty($input['channelType'])) {
            $builder->where('channel', (string)$input['channelType']);
        }

        if (!empty($input['active'])) {
            $builder->where("active", (bool)$input['active']);
        }

        if (!empty($input['userId'])) {
            $builder->where('user_id', (int)$input['userId']);
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate('created_at', $input['createdAt']);
        }

        if (!empty($input['updatedAt'])) {
            $builder->whereDate('updated_at', $input['updatedAt']);
        }

        if (!empty($input['token'])) {
            $builder->where('token', $input['token']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = Group::make([
            Input::make('id')->value($input['id'])->type('number')->title('ID'),

            Select::make('notificationKey')
                ->options(NotificationType::getSelectList())
                ->value($input['notificationKey'])
                ->empty()
                ->title('Тип оповещения'),

            Select::make('channelType')
                ->options(NotificationChannel::getSelectList())
                ->value($input['channelType'])
                ->empty()
                ->title('Канал'),

            Select::make('userId')
                ->fromModel(User::class, 'name', 'id')
                ->value($input['userId'])
                ->empty('Все')
                ->title('Пользователь'),
        ]);

        $group2 = Group::make([
            Select::make('active')
                ->options([1 => 'Активные', 0 => 'Неактивные'])
                ->value($input['active'])
                ->empty('Все')
                ->title('Состояние'),
            Input::make('token')->value($input['token'])->title('Token'),
            Input::make('createdAt')->value($input['createdAt'])->type('date')->title('Дата создания'),
            Input::make('updatedAt')->value($input['updatedAt'])->type('date')->title('Дата обновления'),
        ]);

        return Layout::rows([$group, $group2, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}