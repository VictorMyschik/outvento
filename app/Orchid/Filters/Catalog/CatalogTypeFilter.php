<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Catalog;

use App\Models\Catalog\CatalogGroup;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class CatalogTypeFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'name',
        'json_link',
    ];

    public static function runQuery()
    {
        return CatalogGroup::filters([self::class])->paginate(20);
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }

        if (!empty($input['name'])) {
            $builder->where('name', 'LIKE', '%' . $input['name'] . '%');
        }

        if (!empty($input['json_link'])) {
            $builder->where('json_link', 'LIKE', '%' . $input['json_link'] . '%');
        }

        return $builder;
    }

    public static function displayFilterCard(Request $request): Rows
    {
        $input = $request->all(self::FIELDS);

        $group = Group::make([
            Input::make('id')->value((string)$input['id'])->type('number')->title('ID'),
            Input::make('name')->value((string)$input['name'])->title('Название'),
            Input::make('json_link')->value((string)$input['json_link'])->title('Ссылка на JSON'),
        ]);

        return Layout::rows([$group, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}
