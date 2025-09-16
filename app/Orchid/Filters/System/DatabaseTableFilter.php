<?php

namespace App\Orchid\Filters\System;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filter;

class DatabaseTableFilter extends Filter
{
    public static function runQuery(string $table, Request $request): LengthAwarePaginator
    {
        $sort = $request->get('sort', 'id');

        $direction = 'ASC';
        if (str_contains($sort, '-')) {
            $sort = str_replace('-', '', $sort);
            $direction = 'DESC';
        }

        return DB::table($table)
            ->orderBy($sort, $direction)
            ->paginate(100, ['*'], 'page', $request->get('page', 1));
    }

    public function run(Builder $builder): Builder
    {
        return $builder;
    }
}
