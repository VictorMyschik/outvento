<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Promo;

use App\Models\Promo\Subscription;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Notifications\Enum\PromoEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class SubscriptionFilter extends Filter
{
    public const array FIELDS = [
        'email',
        'event',
        'token',
        'created_at',
    ];

    public static function runQuery()
    {
        return Subscription::filters([self::class]);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['email'])) {
            $builder->where('email', 'like', '%' . $input['email'] . '%');
        }

        if (!empty($input['token'])) {
            $builder->where('token', $input['token']);
        }

        if (!empty($input['event'])) {
            $builder->where('event', $input['event']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('email')
                    ->value($input['email'])
                    ->title('Email'),
                Select::make('event')
                    ->options(PromoEvent::getSelectList())
                    ->empty('Все типы')
                    ->value($input['event'])->title('Тип'),
                Input::make('token')
                    ->value($input['token'])
                    ->title('Token'),
            ]),
            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
