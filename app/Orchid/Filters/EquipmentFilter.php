<?php

namespace App\Orchid\Filters;

use App\Models\CategoryEquipment;
use App\Models\Equipment;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class EquipmentFilter extends Filter
{
    public const array FIELDS = [
        'category',
        'name',
    ];

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all();

        if (isset($input['name']) && $value = htmlspecialchars((string)$input['name'], ENT_QUOTES)) {
            $builder->where('name', 'LIKE', '%' . $value . '%');
        }

        if ($input['category'] ?? null) {
            $result = array_intersect($input['category'], self::getCategoryList());

            if (count($result) !== 0) {
                $builder->join(CategoryEquipment::getTableName(), Equipment::getTableName() . '.category_id', '=', CategoryEquipment::getTableName() . '.id');
                $builder->whereIn(CategoryEquipment::getTableName() . '.name', $result);
            }
        }

        return $builder;
    }

    public static function runQuery()
    {
        return Equipment::filters([self::class])->paginate(30);
    }

    public static function displayFilterCard(): Rows
    {
        return Layout::rows([
            Group::make([
                Select::make('category')
                    ->options(self::getCategoryList())
                    ->multiple()
                    ->value(request()->get('category'))
                    ->title('Category'),

                Input::make('name')->value(request()->get('name'))->title('Name'),
            ]),

            ViewField::make('')->view('space'),
            ActionFilterPanel::getActionsButtons(),
        ]);
    }

    private static function getCategoryList(): array
    {
        $category = array_unique(array_column(CategoryEquipment::all()->toArray(), 'name'));

        return array_combine($category, $category);
    }
}
