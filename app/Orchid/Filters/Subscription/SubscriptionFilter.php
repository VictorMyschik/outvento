<?php

namespace App\Orchid\Filters\Subscription;

use App\Models\Subscription\Subscription;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\Notifications\Enum\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class SubscriptionFilter extends Filter
{
    public static function getFilterFields(): array
    {
        return [
            'email',
            'token',
            'created_at',
        ];
    }

    public static function runQuery()
    {
        return Subscription::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::getFilterFields());

        $builder->where('type', EventType::News->value);

        if (!empty($input['email'])) {
            $builder->where('email', 'like', '%' . $input['email'] . '%');
        }

        if (!empty($input['token'])) {
            $builder->where('token', $input['token']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::getFilterFields());

        return Layout::rows([
            Group::make([
                Input::make('email')->value($input['email'])->title('Email'),
                Input::make('token')->value($input['token'])->title('Token'),
            ]),
            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
