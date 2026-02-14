<?php

declare(strict_types=1);

namespace App\Orchid\Filters\MessageLog;

use App\Models\Notification\ServiceNotification;
use App\Models\User;
use App\Models\UserInfo\Communication;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\ServiceEvent;
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

class UserServiceNotificationFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'userId',
        'eventType',
        'channel',
        'active',
        'token',
        'createdAt',
        'updatedAt',
    ];

    public static function runQuery(): Builder
    {
        return ServiceNotification::filters([self::class])->selectRaw(implode(',', [
            ServiceNotification::getTableName() . '.id',
            ServiceNotification::getTableName() . '.*',
            Communication::getTableName() . '.address as communication_address',
            User::getTableName() . '.subscription_token as token',
            User::getTableName() . '.email as email'
        ]));
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        $builder->join(User::getTableName(), User::getTableName() . '.id', '=', ServiceNotification::getTableName() . '.user_id');
        $builder->join(Communication::getTableName(), Communication::getTableName() . '.id', '=', ServiceNotification::getTableName() . '.communication_id');

        if (!empty($input['id'])) {
            $builder->where(ServiceNotification::getTableName() . '.id', (int)$input['id']);
        }

        if (isset($input['active'])) {
            $builder->where('active', (bool)$input['active']);
        }

        if (!empty($input['eventType'])) {
            $builder->where('event', (string)$input['eventType']);
        }

        if (!empty($input['channel'])) {
            $builder->where(ServiceNotification::getTableName() . '.channel', (string)$input['channel']);
        }

        if (!empty($input['userId'])) {
            $builder->where(ServiceNotification::getTableName() . '.user_id', (int)$input['userId']);
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate(ServiceNotification::getTableName() . '.created_at', $input['createdAt']);
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
                ->options(ServiceEvent::getSelectList())
                ->value($input['eventType'])
                ->empty('Все')
                ->title('Тип оповещения'),

            Select::make('channel')
                ->options(NotificationChannel::getSelectList())
                ->value($input['channel'])
                ->empty('Все')
                ->title('Канал'),

            Relation::make('userId')
                ->fromModel(User::class, 'name', 'id')
                ->value($input['userId'])
                ->title('Пользователь'),

            Input::make('createdAt')->value($input['createdAt'])->type('date')->title('Дата создания'),
        ]);

        return Layout::rows([$group, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}
