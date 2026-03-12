<?php

declare(strict_types=1);

namespace App\Orchid\Filters\User;

use App\Models\User;
use App\Models\UserNotification;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\System\Enum\Language;
use App\Services\User\Enum\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class UserNotificationFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'userId',
        'message',
        'readAt',
        'createdAt',
    ];

    public static function runQuery(): Builder
    {
        return UserNotification::filters([self::class])->orderBy('id', 'desc');
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['message'])) {
            $builder->where('message', 'like', '%' . $input['message'] . '%');
        }

        if (!empty($input['createdAt'])) {
            $builder->whereDate('created_at', $input['createdAt']);
        }

        if (!empty($input['readAt'])) {
            $builder->whereDate('read_at', $input['readAt']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $outLine[] = Input::make('id')
            ->type('number')
            ->value($input['id'])
            ->title('ID');

        $outLine[] = Input::make('message')
            ->value($input['message'])
            ->title('Message');

        $outLine[] = DateTimer::make('createdAt')
            ->title('Created')
            ->format('d.m.Y')
            ->value($input['createdAt']);

        $outLine[] = DateTimer::make('readAt')
            ->title('readAt')
            ->format('d.m.Y')
            ->value($input['Read']);

        return Layout::rows([
            Group::make($outLine),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
