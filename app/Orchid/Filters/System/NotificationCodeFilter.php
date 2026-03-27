<?php

declare(strict_types=1);

namespace App\Orchid\Filters\System;

use App\Models\NotificationCode;
use App\Models\User;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Notifications\Enum\NotificationChannel;
use App\Services\Notifications\Enum\SystemEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class NotificationCodeFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'userId',
        'channel',
        'code',
        'type',
        'data',
        'created_at',
    ];

    public static function runQuery()
    {
        return NotificationCode::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['userId'])) {
            $builder->where('user_id', (int)$input['userId']);
        }

        if (!empty($input['channel'])) {
            $builder->where('channel', (string)$input['channel']);
        }

        if (!empty($input['code'])) {
            $builder->where('code', (string)$input['code']);
        }

        if (!empty($input['type'])) {
            $builder->where('type', (string)$input['type']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('id')
                    ->type('number')
                    ->value($input['id'])
                    ->title('ID'),

                Select::make('channel')
                    ->title('Channel')
                    ->value($input['channel'])
                    ->options(NotificationChannel::getSelectList())
                    ->empty('Select Channel'),

                Input::make('code')
                    ->type('text')
                    ->value($input['code'])
                    ->title('Code'),

                Relation::make('userId')
                    ->title('User ID')
                    ->value($input['userId'])
                    ->fromModel(User::class, 'email', 'id'),

                Select::make('type')
                    ->title('Type')
                    ->empty('Select type')
                    ->value($input['type'])
                    ->options(SystemEvent::getSelectList()),
            ]),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
