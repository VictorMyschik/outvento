<?php

declare(strict_types=1);

namespace App\Orchid\Filters\MessageLog;

use App\Models\MessageLog\TelegramLog;
use App\Models\User;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Notifications\Enum\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

final class MessageLogTelegramFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'type',
        'userId',
        'userTg',
        'message',
        'createdAt',
    ];

    public static function runQuery(): Builder
    {
        return TelegramLog::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['type'])) {
            $builder->where('type', (int)$input['type']);
        }

        if (!empty($input['userId'])) {
            $builder->where('user_id', (int)$input['userId']);
        }

        if (!empty($input['userTg'])) {
            $builder->where('user_tg', 'like', '%' . $input['userTg'] . '%');
        }

        if (!empty($input['message'])) {
            $builder->whereRaw("lower(message) like ?", ['%' . mb_strtolower($input['message']) . '%']);
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate('created_at', $input['createdAt']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = Group::make([
            Select::make('type')
                ->options(EventType::getSelectList())
                ->value($input['type'])
                ->empty()
                ->title('Тип письма'),

            Select::make('userId')
                ->fromModel(User::class, 'name', 'id')
                ->value($input['userId'])
                ->empty('Все')
                ->title('Пользователь'),

            Input::make('id')->value($input['id'])->type('number')->title('ID'),
            Input::make('message')->value($input['message'])->type('text')->title('Сообщение'),
            Input::make('userTg')->value($input['userTg'])->type('text')->title('Пользователь TG'),
            Input::make('createdAt')->value($input['createdAt'])->type('date')->title('Дата отправки'),
        ]);

        return Layout::rows([$group, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}
