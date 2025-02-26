<?php

namespace App\Console\Commands;

use App\Console\Commands\Lego\CacheClearTrait;
use App\Console\Commands\Lego\ConsoleMessagesTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class purge extends Command
{
    use ConsoleMessagesTrait;
    use CacheClearTrait;

    public $signature = 'purge';

    protected $description = 'Rebuild database schema, clear cache, clear logs';

    public function handle(): void
    {
        if (ENV('PRODUCTION') === true) {
            $this->err('ERROR: USING FOR DEVELOPMENT ONLY');
            $this->nl();

            return;
        }

        $this->refreshTables();
        $this->addPostgresTablesData();

        $this->clearCache();

        $this->nl();
        $this->success('FINISH');
        $this->nl();
    }

    private function refreshTables(): void
    {
        $statements = [
            "DROP SCHEMA IF EXISTS public CASCADE;",
            "CREATE SCHEMA public;",
            "GRANT ALL ON SCHEMA public TO public;",
        ];

        foreach ($statements as $statement) {
            $result = DB::statement($statement);
            $result ? $this->success($statement) : $this->err($statement);
            $this->nl();
        }

        $this->nl();
        $this->success('migration tables');

        Artisan::call('migrate');

        // Update timestamp triggers
        $manager = new PostgreSQLTriggerManager();
        $manager->createUpdateTimestampFunction();
        $manager->addTriggersToAllTables();

        $this->success(' is OK');
        $this->nl();
    }

    private function addPostgresTablesData(): void
    {
        $this->nl();
        $this->success('Add data');
        $this->nl();

        $tableList = [
            'settings',
            'users',
            'cron',
            'translates',
            'countries',
            'travel_types'
        ];

        foreach ($tableList as $tableName) {
            $path = __DIR__ . "/purge_data/$tableName.sql";
            if (!is_file($path)) {
                continue;
            }

            $file = file_get_contents($path);

            if (strlen($file) < 5) {
                continue;
            }

            DB::unprepared($file);
            DB::statement("SELECT pg_catalog.setval(pg_get_serial_sequence('$tableName', 'id'), MAX(id)) FROM $tableName;");

            $this->success('Table ' . $tableName . ' is OK');
            $this->nl();
        }
    }
}
