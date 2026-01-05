<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Newsletter;

use App\Models\News\News;
use App\Models\News\NewsInSubgroup;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
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

class NewsFilter extends Filter
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function getFilterFields(): array
    {
        return [
            'active',
            'public',
            'title',
            'group_id',
            'subgroup_id',
            'language'
        ];
    }

    public static function runQuery()
    {
        return News::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::getFilterFields());

        if (!is_null($input['active'])) {
            $builder->where('active', (bool)$input['active']);
        }

        // subgroup_id
        if (!empty($input['subgroup_id'])) {
            $builder->join(NewsInSubgroup::getTableName(), News::getTableName() . '.id', '=', NewsInSubgroup::getTableName() . '.subgroup_id');
            $builder->where(NewsInSubgroup::getTableName() . '.subgroup_id', (int)$input['subgroup_id']);
        }

        if (!is_null($input['public'])) {
            $builder->where('public', (bool)$input['public']);
        }

        if (!empty($input['language'])) {
            $builder->where('language', $input['language']);
        }

        if (!empty($input['title'])) {
            $builder->where('title', 'like', '%' . $input['title'] . '%');
        }

        if (!empty($input['group_id'])) {
            $builder->where('group_id', (int)$input['group_id']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::getFilterFields());

        return Layout::rows([
            Group::make([
                Select::make('active')
                    ->options([null => 'Все', 1 => 'Активные', 0 => 'Не активные'])
                    ->value($input['active'])
                    ->title('Активно (новости)'),

                Select::make('public')
                    ->options([null => 'Все', 1 => 'Активные', 0 => 'Не активные'])
                    ->value($input['public'])
                    ->title('Опубликовано (новости)'),

                Select::make('language')
                    ->options(Language::getSelectList())
                    ->value($input['language'])
                    ->empty('Все')
                    ->title('Язык'),
            ]),


            Input::make('title')->value($input['title'])->title('Наименование'),
            Input::make('group_id')->hidden()->value($input['group_id']),

            ViewField::make('')->view('space'),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
