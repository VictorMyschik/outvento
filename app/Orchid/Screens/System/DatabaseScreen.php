<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Orchid\Filters\MigrationFilter;
use App\Orchid\Layouts\FileDownloadLayout;
use App\Orchid\Layouts\System\FileUploadLayout;
use App\Orchid\Layouts\System\MigrationLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\ViewField;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use PDO;
use Symfony\Component\Console\Output\BufferedOutput;

class DatabaseScreen extends Screen
{
    private const string DIRECTORY = 'tmp/dumps';

    public string $name = 'Database';
    public string $description = 'Database management and information';

    public function commandBar(): iterable
    {
        return [
            Button::make('Run migration')->confirm('Run migration?')->method('runMigration'),
        ];
    }

    public function query(): iterable
    {
        return [
            'migration-list' => MigrationFilter::runQuery()->orderBy('id', 'desc')->paginate(10),
        ];
    }

    public function layout(): iterable
    {
        return [
            MigrationLayout::class,
            Layout::rows([
                Group::make([
                    ViewField::make('')->view('admin.table_raw')->value($this->getPostgreTableList()),
                    //ViewField::make('')->view('admin.table_raw')->value($this->getClickhouseTableList()),
                ]),
            ]),
            Layout::modal('download_clickhouse_table', FileDownloadLayout::class)->async('asyncGetDownloadClickhouseTableDump')->withoutApplyButton(),
            Layout::modal('download_postgre_table', FileDownloadLayout::class)->async('asyncGetDownloadPostgreTableDump')->withoutApplyButton(),
            Layout::modal('restore_dump_clickhouse', FileUploadLayout::class),
            Layout::modal('insert_clickhouse_data', FileUploadLayout::class),
            Layout::modal('insert_postgre_data', FileUploadLayout::class),
        ];
    }

    public function asyncGetDownloadClickhouseDump(): array
    {
        return [
            'fileName'     => $this->dumpClickHouse(),
            'downloadName' => 'clickhouse_dump.sql',
            'disk'         => 'local',
        ];
    }

    public function asyncGetDownloadClickhouseTableDump(string $tableName): array
    {
        return [
            'fileName'     => $this->dumpClickHouseTable($tableName),
            'downloadName' => $tableName . '_dump.tsv',
            'disk'         => 'local',
        ];
    }

    public function asyncGetDownloadPostgreTableDump(string $tableName): array
    {
        return [
            'fileName'     => $this->dumpPostgreTable($tableName),
            'downloadName' => $tableName . '_dump.sql',
            'disk'         => 'local',
        ];
    }

    private function dumpPostgreTable(string $table): string
    {
        if (!preg_match('/^[A-Za-z0-9_\.]+$/', $table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }

        $schema = 'public';

        $dir = self::DIRECTORY;
        Storage::disk('local')->makeDirectory($dir);

        $database = config('database.connections.pgsql.database');
        $timestamp = now()->format('Ymd_His');

        $relativePath = "{$dir}/postgres_data_{$database}_{$schema}_{$table}_{$timestamp}.tsv";
        $fullPath = Storage::disk('local')->path($relativePath);

        // Открываем файл для записи
        $fp = fopen($fullPath, 'wb');
        if (!$fp) {
            throw new \RuntimeException("Cannot create file: {$fullPath}");
        }

        // Получаем нативное PGSQL соединение
        $pdo = DB::connection('pgsql')->getPdo();

        try {
            $pdo->pgsqlCopyToFile($table, $fullPath);
        } catch (\Throwable $e) {
            fwrite($fp, "-- ERROR DURING COPY:\n-- " . $e->getMessage());
            fclose($fp);

            return $relativePath;
        }

        fclose($fp);

        return $relativePath;
    }


    public function dumpClickHouseTable(string $table): string
    {
        $baseUrl = (config('database.connections.clickhouse.https') ? 'https://' : 'http://')
            . config('database.connections.clickhouse.host')
            . ':' . config('database.connections.clickhouse.port');

        $database = config('database.connections.clickhouse.database');
        $username = config('database.connections.clickhouse.username');
        $password = config('database.connections.clickhouse.password');

        $dir = self::DIRECTORY ?? 'dumps';
        Storage::disk('local')->makeDirectory($dir);

        $relativePath = $dir . '/clickhouse_dump_' . $database . '_' . $table . '_' . now()->format('Ymd_His') . '.tsv';
        $fullPath = Storage::disk('local')->path($relativePath);

        // Открываем файл и пишем наш красивый заголовок
        $handle = fopen($fullPath, 'wb');
        fwrite($handle, "# ClickHouse TSVRawWithNamesAndTypes dump\n");
        fwrite($handle, "# Database: {$database}\n");
        fwrite($handle, "# Table: {$table}\n");
        fwrite($handle, "# Generated: " . now()->toDateTimeString() . "\n");
        fwrite($handle, "# Format: TSVRawWithNamesAndTypes — survives any garbage\n\n");

        $query = "SELECT * FROM `{$database}`.`{$table}` FORMAT TSVRawWithNamesAndTypes";

        $url = $baseUrl . '/?' . http_build_query([
                'query'              => $query,
                'max_execution_time' => 0,
                'max_threads'        => 16,
            ]);

        $clientOptions = [
            'sink'    => $handle,
            'timeout' => 0,
        ];

        if ($username || $password) {
            $clientOptions['auth'] = [$username ?? '', $password ?? ''];
        }

        $response = Http::withOptions($clientOptions)->get($url);

        if (!$response->successful()) {
            fwrite($handle, "\n# ERROR: " . $response->body() . "\n");
            fclose($handle);
            throw new \RuntimeException('Ошибка при выгрузке из ClickHouse: ' . $response->status());
        }

        // Футер
        fwrite($handle, "\n# END OF TABULATED DATA FOR {$database}.{$table}\n");
        fclose($handle);

        return $relativePath;
    }

    private function getPostgreTableList(): array
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
    t.table_name AS table_name,
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
            $rows[] = ['<b>Таблица PostgreSQL</b>', '<b>Количество строк</b>', '<b>Действия</b>'];
            foreach ($tables as $table) {
                $r = (array)$table;
                $rows[] = [
                    'Таблица'          => '<a href="' . route('system.database.table', ['database' => 'pgsql', 'table' => $r['table_name']]) . '">' . $r['table_name'] . '</a>',
                    'Количество строк' => $r['реальное_количество_строк'],
                    '#'                =>
                        DropDown::make()->icon('options-vertical')->list([
                            Button::make('Drop')
                                ->confirm('Are you sure you want to drop Clickhouse table: ' . $r['table_name'] . '? This action cannot be undone.')
                                ->icon('trash')
                                ->method('dropPostgreTable', ['tableName' => $r['table_name']]),
                            Button::make('Truncate')
                                ->confirm('Are you sure you want to Truncate Clickhouse table: ' . $r['table_name'] . '? This action cannot be undone.')
                                ->icon('trash')
                                ->method('truncatePostgreTable', ['tableName' => $r['table_name']]),
                            ModalToggle::make('Tsv Dump')
                                ->modal('download_postgre_table')
                                ->icon('download')
                                ->method('downloadPostgreTableDump', ['tableName' => $r['table_name']])
                                ->modalTitle('Download Postgre Table Dump: ' . $r['table_name']),
                            ModalToggle::make('Tsv Insert')
                                ->modal('insert_postgre_data')
                                ->icon('upload')
                                ->method('insertPostgreTableDump', ['tableName' => $r['table_name']])
                                ->modalTitle('Insert Postgre Table Dump (.tsv): ' . $r['table_name']),
                        ]),
                ];
            }
        } catch (\Exception $e) {
            echo "Ошибка: " . $e->getMessage() . "\n";
        }


        return $rows;
    }

    public function insertPostgreTableDump(Request $request, ?string $tableName = null): void
    {
        $path = $request->file('file')->getRealPath();
        $pdo = DB::getPdo();
        $pdo->beginTransaction();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $pdo->pgsqlCopyFromFile(tableName: $tableName, filename: $path);

        if ($result) {
            $pdo->commit();
            Toast::info('Успешно импортировано в таблицу ' . $tableName)->delay(2000);
        } else {
            $pdo->rollBack();
            Toast::error('Ошибка при импорте в таблицу ' . $tableName)->delay(5000);
        }
    }

    public function dropPostgreTable(string $tableName): void
    {
        DB::statement('DROP TABLE IF EXISTS ' . $tableName . ' CASCADE;');
    }

    public function truncatePostgreTable(string $tableName): void
    {
        DB::statement('TRUNCATE TABLE ' . $tableName . ';');
    }

    private function getClickhouseTableList(): array
    {
        $rows = [];

        try {
            $tables = DB::connection('clickhouse')->select(<<<'SQL'
            SELECT t.name       AS table_name,
                   t.total_rows AS row_count
            FROM system.tables AS t
            WHERE t.database = 'weekler'
            ORDER BY t.name;
        SQL
                , ['weekler']);

            $rows[] = ['<b>Таблица Clickhouse</b>', '<b>Количество строк</b>', '<b>Действия</b>'];

            foreach ($tables as $table) {
                $r = (array)$table;

                $link = '<a href="' . route('system.database.table', ['database' => 'clickhouse', 'table' => $r['table_name'],]) . '">' . $r['table_name'] . '</a>';

                $rows[] = [
                    'Таблица'          => $link,
                    'Количество строк' => $r['row_count'],
                    '#'                =>
                        DropDown::make()->icon('options-vertical')->list([
                            Button::make('Drop')
                                ->confirm('Are you sure you want to drop Clickhouse table: ' . $r['table_name'] . '? This action cannot be undone.')
                                ->icon('trash')
                                ->method('dropClickhouseTable', ['tableName' => $r['table_name']]),
                            Button::make('Truncate')
                                ->confirm('Are you sure you want to Truncate Clickhouse table: ' . $r['table_name'] . '? This action cannot be undone.')
                                ->icon('trash')
                                ->method('truncateClickhouseTable', ['tableName' => $r['table_name']]),
                            Button::make('optimize')
                                ->confirm('Are you sure you want to optimize Clickhouse table: ' . $r['table_name'] . '?')
                                ->icon('refresh')
                                ->method('optimizeClickhouseTable', ['tableName' => $r['table_name']]),
                            ModalToggle::make('Tsv Dump')
                                ->modal('download_clickhouse_table')
                                ->icon('download')
                                ->method('downloadClickhouseTableDump', ['tableName' => $r['table_name']])
                                ->modalTitle('Download Clickhouse Table Dump: ' . $r['table_name']),
                            ModalToggle::make('Tsv Insert')
                                ->modal('insert_clickhouse_data')
                                ->icon('upload')
                                ->method('insertClickhouseTableDump', ['tableName' => $r['table_name']])
                                ->modalTitle('Insert Clickhouse Table Dump (.tsv): ' . $r['table_name']),
                        ]),
                ];
            }
        } catch (\Exception $e) {
            echo "Ошибка ClickHouse: " . $e->getMessage();
        }

        return $rows;
    }

    public function dropClickhouseTable(string $tableName): void
    {
        try {
            DB::connection('clickhouse')->statement("DROP TABLE IF EXISTS `weekler`.`{$tableName}`");
            Toast::info("Table {$tableName} dropped successfully from ClickHouse.")->delay(2000);
        } catch (\Exception $e) {
            Toast::error("Error dropping table {$tableName} from ClickHouse: " . $e->getMessage())->delay(5000);
        }
    }

    public function truncateClickhouseTable(string $tableName): void
    {
        try {
            DB::connection('clickhouse')->statement("TRUNCATE TABLE `weekler`.`{$tableName}`");
            Toast::info("Table {$tableName} truncated successfully in ClickHouse.")->delay(2000);
        } catch (\Exception $e) {
            Toast::error("Error truncating table {$tableName} in ClickHouse: " . $e->getMessage())->delay(5000);
        }
    }

    public function insertClickhouseTableDump(Request $request, ?string $tableName = null): void
    {
        $request->validate(['file' => 'required|file']);

        $uploadedFile = $request->file('file');

        $targetDatabase = $request->input('database', 'weekler');
        $targetTable = $tableName ?? $request->input('table');

        $baseUrl = (config('database.connections.clickhouse.https') ? 'https://' : 'http://')
            . config('database.connections.clickhouse.host')
            . ':' . config('database.connections.clickhouse.port');

        $username = config('database.connections.clickhouse.username');
        $password = config('database.connections.clickhouse.password');

        // Ключевая строчка — этот формат проглатывает ВСЁ
        $query = "INSERT INTO `{$targetDatabase}`.`{$targetTable}` FORMAT TSVRawWithNamesAndTypes";

        $url = $baseUrl . '/?' . http_build_query([
                'query'              => $query,
                'max_insert_threads' => 16,
                'max_execution_time' => 0,
            ]);

        try {
            $fileResource = fopen($uploadedFile->getRealPath(), 'rb');

            $response = Http::timeout(0)
                ->withBasicAuth($username ?? '', $password ?? '')
                ->withBody($fileResource, 'application/octet-stream')
                ->post($url);

            if (!$response->successful()) {
                $error = $response->body();
                Toast::error($error)->delay(2000);
            }

            Toast::success("Table {$targetTable} successfully truncated.")->delay(2000);

        } catch (\Throwable $e) {
            Toast::error($e->getMessage())->delay(5000);
        }
    }

    public function runRefreshMigrationFile(string $migration): void
    {
        $buffer = new BufferedOutput();
        Artisan::call('migrate:refresh', ['--path' => 'database/migrations/' . $migration . '.php', '--force' => true], $buffer);

        Toast::info($buffer->fetch())->delay(2000);
    }

    public function optimizeClickhouseTable(string $tableName): void
    {
        DB::connection('clickhouse')->statement("OPTIMIZE TABLE {$tableName} FINAL SETTINGS optimize_skip_merged_partitions = 1");
    }

    public function runMigration(): void
    {
        $buffer = new BufferedOutput();

        Artisan::call('migrate', ['--force' => true], $buffer);

        Toast::info($buffer->fetch());
    }

    public function remove(int $id): void
    {
        DB::table('migrations')->where('id', $id)->delete();
    }
}
