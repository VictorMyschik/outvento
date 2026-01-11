<?php

namespace App\Orchid\Filters\Catalog;

use App\Models\Catalog\CatalogAttribute;
use App\Models\Catalog\CatalogGroup;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class CatalogAttributeFilter extends Filter
{
    public const array FIELDS = [
        'active',
        'group_id',
        'filter_active',
        'filter_sort',
    ];

    public static function runQuery()
    {
        return CatalogGroup::filters([self::class])->paginate(50);
    }

    public function run(Builder $builder): Builder
    {
        $input = request()->all(self::FIELDS);

        if (!is_null($input['filter_active'])) {
            $builder->where(CatalogGroup::getTableName() . '.active', (bool)$input['active']);
        }

        if (!empty($input['group_id'])) {
            $builder->where(CatalogGroup::getTableName() . '.id', (int)$input['group_id']);
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = Group::make([
            Select::make('active')
                ->options([null => 'Все', 1 => 'Активные', 0 => 'Не активные'])
                ->value($input['active'])
                ->title('Активно'),

            Select::make('id')
                ->options([null => 'Все'] + CatalogGroup::all()->pluck('name', 'id')->toArray())
                ->value($input['group_id'])
                ->title('Группы продуктов'),
        ]);

        return Layout::rows([$group, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}
