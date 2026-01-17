<?php

declare(strict_types=1);

namespace App\Orchid\Filters\MessageLog;

use App\Models\Notification\UserNotificationSetting;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Models\UserInfo\CommunicationType;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Notifications\Enum\EventType;
use App\Services\Notifications\Enum\NotificationChannel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserNotificationSettingFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'active',
        'userId',
        'eventType',
        'channelType',
        'active',
        'token',
        'createdAt',
        'updatedAt',
    ];

    public static function runQuery(): Builder
    {
        return UserNotificationSetting::filters([self::class])->selectRaw(implode(',', [
            UserNotificationSetting::getTableName() . '.id',
            UserNotificationSetting::getTableName() . '.*',
            CommunicationType::getTableName() . '.name_ru as communication_type',
            Communication::getTableName() . '.address as communication_address',
            User::getTableName() . '.subscription_token as token',
            User::getTableName() . '.email as email'
        ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        $builder->join(User::getTableName(), User::getTableName() . '.id', '=', UserNotificationSetting::getTableName() . '.user_id');
        $builder->join(Communication::getTableName(), Communication::getTableName() . '.id', '=', UserNotificationSetting::getTableName() . '.communication_id');
        $builder->join(CommunicationType::getTableName(), CommunicationType::getTableName() . '.id', '=', Communication::getTableName() . '.type_id');

        if (!empty($input['id'])) {
            $builder->where(UserNotificationSetting::getTableName() . '.id', (int)$input['id']);
        }

        if (isset($input['active'])) {
            $builder->where('active', (bool)$input['active']);
        }

        if (!empty($input['eventType'])) {
            $builder->where('event_type', (string)$input['eventType']);
        }

        if (!empty($input['channelType'])) {
            $builder->where(CommunicationType::getTableName() . '.id', (string)$input['channelType']);
        }

        if (!empty($input['active'])) {
            $builder->where("active", (bool)$input['active']);
        }

        if (!empty($input['userId'])) {
            $builder->where(UserNotificationSetting::getTableName() . '.user_id', (int)$input['userId']);
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate(UserNotificationSetting::getTableName() . '.created_at', $input['createdAt']);
        }

        if (!empty($input['updatedAt'])) {
            $builder->whereDate(UserNotificationSetting::getTableName() . '.updated_at', $input['updatedAt']);
        }

        if (!empty($input['token'])) {
            $builder->where('subscription_token', $input['token']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = Group::make([
            Input::make('id')->value($input['id'])->type('number')->title('ID'),

            Select::make('eventType')
                ->options(EventType::getSelectList())
                ->value($input['eventType'])
                ->empty()
                ->title('Тип оповещения'),

            Select::make('channelType')
                ->options(NotificationChannel::getSelectList())
                ->value($input['channelType'])
                ->empty()
                ->title('Канал'),

            Relation::make('userId')
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
