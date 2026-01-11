<?php

declare(strict_types=1);

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

class CatalogAttributeGroupFilter extends Filter
{
    private static array $fields = [
        'active',
        'group_id',
    ];

    public static function runQuery(int $attributeGroupID)
    {
        return CatalogAttribute::filters([self::class])->where('group_attribute_id', $attributeGroupID)->orderBy('sort')->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    public static function getFilterFields(): array
    {
        return self::$fields;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::getFilterFields());

        $group = Group::make([
            Select::make('active')
                ->options([null => 'Все', 1 => 'Активные', 0 => 'Не активные'])
                ->value($input['active'])
                ->title('Активно'),

            Select::make('group_id')
                ->options([null => 'Все'] + CatalogGroup::all()->pluck('name', 'id')->toArray())
                ->value($input['group_id'])
                ->title('Группы'),
        ]);

        return Layout::rows([$group,  ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}
