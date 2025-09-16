<?php

namespace App\Orchid\Layouts\System;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class RawTableLayout extends Table
{
    protected $target = 'list';

    protected function columns(): iterable
    {
        $titles = $this->query->get('list')->first();
        if (!$titles) {
            return [];
        }
        $columns = array_keys((array)$titles);

        $columns = array_map(function ($column) {
            return TD::make($column, $column)->sort()->render(function ($value) use ($column) {
                return '<span class="text-nowrap">' . $value->{$column} . '</span>';
            });
        }, $columns);

        return $columns;
    }

    public function hoverable(): bool
    {
        return true;
    }
}
