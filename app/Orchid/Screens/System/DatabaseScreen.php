<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class DatabaseScreen extends Screen
{
    public string $name = 'Database';
    public string $description = 'Database management and information';

    public function commandBar(): iterable
    {
        return [];
    }

    public function query(): iterable
    {

        return [];
    }

    public function layout(): iterable
    {
        $list = $this->getTableList();

        return [
            Layout::rows([
                ViewField::make('')->view('admin.table_raw')->value($list),
            ])
        ];
    }

    private function getTableList(): array
    {
        try {
            $tables = DB::transaction(function () {
                DB::statement(
                    <<<SQL
DO $$
    DECLARE
        table_rec RECORD;
    BEGIN
        FOR table_rec IN (
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
              AND table_type = 'BASE TABLE'
        )
        LOOP
            EXECUTE format(
                'CREATE TEMP TABLE IF NOT EXISTS temp_row_counts (table_name text, row_count bigint);
                 INSERT INTO temp_row_counts
                 SELECT %L, COUNT(*) FROM %I.%I',
                table_rec.table_name, 'public', table_rec.table_name
            );
        END LOOP;
    END $$;
SQL
                );

                $results = DB::select(
                    <<<SQL
SELECT
    t.table_name AS имя_таблицы,
    COALESCE(trc.row_count, 0) AS реальное_количество_строк
FROM information_schema.tables t
LEFT JOIN temp_row_counts trc ON t.table_name = trc.table_name
WHERE t.table_schema = 'public'
  AND t.table_type = 'BASE TABLE'
ORDER BY t.table_name;
SQL
                );

                DB::statement('DROP TABLE IF EXISTS temp_row_counts;');

                return $results;
            });

            // Вывод результатов
            $rows[] = ['<b>Таблица</b>', '<b>Количество строк</b>'];
            foreach ($tables as $table) {
                $r = (array)$table;
                $rows[] = [
                    'Таблица'          => '<a href="' . route('system.database.table', ['table' => $r['имя_таблицы']]) . '">' . $r['имя_таблицы'] . '</a>',
                    'Количество строк' => $r['реальное_количество_строк'],
                ];
            }
        } catch (\Exception $e) {
            echo "Ошибка: " . $e->getMessage() . "\n";
        }


        return $rows;
    }
}
