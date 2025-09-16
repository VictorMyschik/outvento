<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Orchid\Filters\System\DatabaseTableFilter;
use App\Orchid\Layouts\System\RawTableLayout;
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

    public function query(string $table): iterable
    {
        $this->name = 'Table: ' . $table;
        $this->description = 'Information about table: ' . $table;

        return [
            'list' => DatabaseTableFilter::runQuery($table, $this->request),
        ];
    }

    public function layout(): iterable
    {
        return [
            RawTableLayout::class,
        ];
    }
}
