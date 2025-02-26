<?php

namespace App\Orchid\Filters;

use App\Models\Subscription\Subscription;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Email\Enum\EmailTypeEnum;
use App\Services\System\Enum\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class EmailSubscriptionFilter extends Filter
{
    public const array FIELDS = ['type', 'email', 'token', 'language'];

    public static function runQuery()
    {
        return Subscription::filters([self::class])->paginate(20);
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

        if (!empty($input['type'])) {
            $builder->where('type', (int)$input['type']);
        }

        if (!empty($input['language'])) {
            $builder->where('language', (int)$input['language']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Select::make('type')->value($input['type'])->empty()->options([EmailTypeEnum::NEWS->value => EmailTypeEnum::NEWS->getLabel()])->title('Тип'),
                Input::make('email')->value($input['email'])->title('Email'),
                Input::make('token')->value($input['token'])->title('Token'),
                Select::make('language')->options(Language::getSelectList())->value($input['language'])->empty()->title('Language'),
            ]),
            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
