<?php

namespace App\Orchid\Filters\System;

use App\Orchid\Layouts\ActionFilterPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class DatabaseTableFilter extends Filter
{
    public const array FIELDS = [
        'sort',
        'filter',
        'limit',
    ];

    public static function runQuery(string $database, string $table, Request $request): LengthAwarePaginator
    {
        $db = DB::connection($database);

        $query = $db->table($table);

        if ($sort = $request->get('sort')) {
            $direction = 'ASC';
            if (str_contains($sort, '-')) {
                $sort = str_replace('-', '', $sort);
                $direction = 'DESC';
            }

            $query->orderBy($sort, $direction);
        }

        if (!empty($request->get('filter'))) {
            $query->whereRaw($request->get('filter'));
        }

        if ($request->get('limit')) {
            $query->limit((int)$request->get('limit'));
        }

        return $query->paginate(100, ['*'], 'page', $request->get('page', 1));
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }

    public static function displayFilterCard(): Rows
    {
        $input = request()->all(self::FIELDS);

        return Layout::rows([
            Group::make([
                Input::make('limit')->value($input['limit'])->title('Limit'),
                Input::make('filter')->value($input['filter'])->title('Filter'),
            ]),

            ViewField::make('')->view('space'),

            ActionFilterPanel::getActionsButtons(),
        ]);
    }
}
