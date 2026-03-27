<?php

declare(strict_types=1);

namespace App\Orchid\Filters\System;

use App\Models\NotificationToken;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Notifications\Enum\ServiceEvent;
use App\Services\Notifications\Enum\NotificationChannel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class NotificationTokenFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'address',
        'channel',
        'type',
        'token',
    ];

    public static function runQuery()
    {
        return NotificationToken::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['address'])) {
            $builder->where('address', 'like', '%' . $input['address'] . '%');
        }

        if (!empty($input['channel'])) {
            $builder->where('channel', (string)$input['channel']);
        }

        if (!empty($input['type'])) {
            $builder->where('type', (string)$input['type']);
        }

        if (!empty($input['token'])) {
            $builder->where('token', $input['token']);
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

                Input::make('address')
                    ->type('text')
                    ->value($input['address'])
                    ->title('Address'),

                Select::make('channel')
                    ->title('Channel')
                    ->value($input['channel'])
                    ->options(NotificationChannel::getSelectList())
                    ->empty('Select Channel'),

                Select::make('type')
                    ->title('Type')
                    ->value($input['type'])
                    ->options(ServiceEvent::getSelectList())
                    ->empty('[Select Type]'),

                Input::make('token')
                    ->value($input['token'])
                    ->type('text')
                    ->title('Token'),
            ]),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
