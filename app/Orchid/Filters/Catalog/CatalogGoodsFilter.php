<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Catalog;

use App\Models\Catalog\CatalogGood;
use App\Models\Catalog\Manufacturer;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class CatalogGoodsFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'name',
        'string_id',
        'manufacturer_id',
        'prefix',
        'group_id',
    ];

    public static function runQuery()
    {
        return CatalogGood::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', $input['id']);
        }

        if (!empty($input['name'])) {
            $builder->where('name', 'LIKE', '%' . $input['name'] . '%');
        }

        if (!empty($input['string_id'])) {
            $builder->where('string_id', 'LIKE', '%' . $input['string_id'] . '%');
        }

        if (!empty($input['manufacturer_id'])) {
            $builder->where('manufacturer_id', $input['manufacturer_id']);
        }

        if (!empty($input['prefix'])) {
            $builder->where('prefix', 'LIKE', '%' . $input['prefix'] . '%');
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = Group::make([
            Input::make('id')->value((string)$input['id'])->title('ID'),
            Input::make('prefix')->value((string)$input['prefix'])->title('Префикс'),
            Input::make('string_id')->value((string)$input['string_id'])->title('Строковый ID'),
            Input::make('name')->value((string)$input['name'])->title('Название'),
            Relation::make('manufacturer_id')
                ->fromModel(Manufacturer::class, 'name')
                ->value((int)$input['manufacturer_id'])
                ->title('Производитель'),
        ]);

        return Layout::rows([$group, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}
