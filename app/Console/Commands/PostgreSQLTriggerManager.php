<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostgreSQLTriggerManager
{
    public function createUpdateTimestampFunction($schema = 'public'): void
    {
        // Check if the schema exists and create it if it does not
        $checkSchemaSql = "CREATE SCHEMA IF NOT EXISTS {$schema};";
        $this->executeQuery($checkSchemaSql);

        $sql = "
    CREATE OR REPLACE FUNCTION {$schema}.update_timestamp()
    RETURNS TRIGGER AS $$
    BEGIN
        NEW.updated_at = NOW(); -- Замените updated_at на имя вашего столбца
        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;";

        $this->executeQuery($sql);
    }

    public function addUpdateTimestampTrigger($tableName): void
    {
        if (!$this->columnExists($tableName, 'updated_at')) {
            Log::info("Column updated_at does not exist in table $tableName.");
            return;
        }

        $sql = sprintf("
        CREATE TRIGGER %s_update_timestamp_trigger
        BEFORE UPDATE ON %s
        FOR EACH ROW EXECUTE FUNCTION update_timestamp();",
            $tableName, $tableName);

        try {
            $r = $this->executeQuery($sql);
        } catch (\Illuminate\Database\QueryException $e) {
            // Проверяем, существует ли триггер
            if ($e->getCode() == "42P07") { // Duplicate Object
                Log::info("Trigger for $tableName already exists.");
            } else {
                throw $e; // Переотправляем другие ошибки
            }
        }
    }

    public function addTriggersToAllTables($schema = 'public'): void
    {
        $tables = DB::table('information_schema.tables')
            ->select('table_name')
            ->where('table_schema', $schema)
            ->where('table_type', 'BASE TABLE')
            ->pluck('table_name');

        foreach ($tables as $table) {
            $this->addUpdateTimestampTrigger($table);
        }
    }

    private function columnExists($tableName, $columnName): bool
    {
        $result = DB::select("
            SELECT column_name
            FROM information_schema.columns
            WHERE table_name = ? AND column_name = ?", [$tableName, $columnName]);

        return !empty($result);
    }

    private function executeQuery($sql): bool
    {
        return DB::statement($sql);
    }
}
