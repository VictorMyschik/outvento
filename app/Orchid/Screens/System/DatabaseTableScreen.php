<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Orchid\Filters\System\DatabaseTableFilter;
use App\Orchid\Layouts\System\RawTableLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class DatabaseTableScreen extends Screen
{
    public string $name = 'Table ';
    public string $description = 'Database management and information';

    public function __construct(private Request $request) {}

    public function commandBar(): iterable
    {
        return [
            Link::make('Назад')->icon('arrow-left')->route('system.database'),
        ];
    }

    public function query(string $database, string $table): iterable
    {
        $this->name = 'Table: ' . $table;
        $this->description = ucfirst($database) . ' | Information about table: ' . $table;

        return [
            'list' => DatabaseTableFilter::runQuery($database, $table, $this->request),
        ];
    }

    public function layout(): iterable
    {
        return [
            DatabaseTableFilter::displayFilterCard(),
            RawTableLayout::class,
        ];
    }

    #region Filter
    public function runFiltering(Request $request): RedirectResponse
    {
        $list = [
            'database' => 'clickhouse',
            'table'    => 'wb_sale_reports',
        ];

        foreach (DatabaseTableFilter::FIELDS as $item) {
            if (!is_null($request->get($item))) {
                $list[$item] = $request->get($item);
            }
        }

        return redirect()->route('system.database.table', $list);
    }

    public function clearFilter(Request $request): RedirectResponse
    {
        return redirect()->route('system.database.table', [
            'database' => 'clickhouse',
            'table'    => 'wb_sale_reports',
        ]);
    }
    #endregion
}
