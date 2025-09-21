<?php

namespace App\Orchid\Filters\Catalog;

use App\Models\Catalog\CatalogGroup;
use App\Orchid\Layouts\Lego\ActionFilterPanel;
use App\Services\Catalog\CatalogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class CatalogFilter extends Filter
{
    public const array FIELDS = [
        'id',
        'active',
        'code',
        'group_id',
        'parent_id',
        'description',
        'created_at',
        'updated_at',
    ];

    public static function runQuery(CatalogService $catalogService, Request $request)
    {
        $withFilters = false;
        $filters = $request->all(self::FIELDS);
        foreach ($filters as $key => $value) {
            if ($key === 'parent_id') {
                continue;
            }
            if (!empty($value)) {
                $withFilters = true;
                break;
            }
        }
        if ($withFilters) {
            return CatalogGroup::filters([self::class])->paginate(20);
        }

        $list = CatalogGroup::filters([self::class])
            ->where(function ($query) use ($filters) {
                return $query->where('parent_id', $filters['parent_id'])->orWhereNull('parent_id');
            })
            ->orderBy('id')
            ->get()->all();

        $groups = $catalogService->getGroupList();

        $rows = [];
        foreach ($list as $group) {
            $rows[] = $group;

            self::getChildGroup($groups, $group, $rows);
        }

        return collect($rows);
    }

    private static function getChildGroup(array $groups, CatalogGroup $group, array &$out): void
    {
        foreach ($groups as $key => $item) {
            if ($item->parent_id !== $group->id) {
                continue;
            }
            $out[] = $item;
            self::getChildGroup($groups, $item, $out);
        }
    }

    public function run(Builder $builder): Builder
    {
        $input = $this->request->all(self::FIELDS);

        if (isset($input['parent_id'])) {
            $builder->where('parent_id', (int)$input['parent_id'] ?: null);
        }

        if (!is_null($input['active'])) {
            $builder->where('active', (bool)$input['active']);
        }

        if (!empty($input['group_id'])) {
            $builder->where('id', (int)$input['group_id']);
        }

        if (!empty($input['code'])) {
            $builder->where('code', 'like', '%' . $input['code'] . '%');
            $builder->orWhere('code_en', 'like', '%' . $input['code'] . '%');
        }

        if (!empty($input['id'])) {
            $builder->where('id', (int)$input['id']);
        }
        if (isset($input['parent_id'])) {
            $builder->where('parent_id', (int)$input['parent_id'] ?: null);
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

            Input::make('id')->value($input['id'])->type('number')->title('ID'),
            Input::make('code')->value($input['code'])->type('text')->title('Символьный код'),
        ]);

        return Layout::rows([$group, ViewField::make('')->view('space'), ActionFilterPanel::getActionsButtons($request->all())]);
    }
}
