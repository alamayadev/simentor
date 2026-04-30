<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;

class SeederUtils
{
    /**
     * Return list of column names for a table.
     */
    public static function getTableColumns(string $table): array
    {
        try {
            return Schema::getColumnListing($table);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Normalize a data row to match the table columns.
     * - Remove keys that don't exist in the table
     * - Fill some safe defaults for commonly-missing columns so seeding doesn't fail
     */
    public static function normalizeRow(string $table, array $row, bool $preserveId = false): array
    {
        $cols = self::getTableColumns($table);
        if (empty($cols)) return $row;

        // Always remove id to let DB assign it, unless caller explicitly wants to keep it
        if (array_key_exists('id', $row) && !$preserveId) unset($row['id']);

        $out = [];
        $now = now();

        foreach ($cols as $col) {
            if (array_key_exists($col, $row)) {
                $out[$col] = $row[$col];
                continue;
            }

            // Provide safe defaults for a small set of known problematic columns
            switch ($col) {
                case 'created_at':
                case 'updated_at':
                    $out[$col] = $now;
                    break;
                case 'last_activity':
                    $out[$col] = 0;
                    break;
                case 'payload':
                    $out[$col] = '';
                    break;
                case 'token':
                case 'remember_token':
                case 'user_agent':
                case 'profile_photo_path':
                    $out[$col] = null;
                    break;
                default:
                    // by default leave absent columns out entirely (DB will use defaults/null if column allows it)
                    break;
            }
        }

        return $out;
    }

    /**
     * Insert many rows into a table using batching when large.
     * - $rows is an array of associative arrays
     * - If count($rows) > 4000 we chunk by $chunkSize
     */
    public static function insertInBatches(string $table, array $rows, int $chunkSize = 500): void
    {
        if (empty($rows)) return;

        $total = count($rows);
        // Defensive chunking: compute a safe number of rows per insert based on
        // the number of bound variables and the DB driver's limit. SQLite has
        // a default limit of 999 variables which is easily exceeded when
        // inserting many rows * many columns in a single statement.
        $numCols = count($rows[0]) ?: 1;

        // detect driver and pick a conservative max bind variables limit
        $driver = 'sqlite';
        try {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) ?: $driver;
        } catch (\Throwable $e) {
            // ignore and fall back to default
        }

        // Set a conservative max variables per statement per driver
        $maxVariables = 65535; // default large
        if (stripos($driver, 'sqlite') !== false) {
            $maxVariables = 999; // SQLite default max variables bound
        } elseif (stripos($driver, 'sqlsrv') !== false) {
            $maxVariables = 2100; // SQL Server limit
        } elseif (stripos($driver, 'mysql') !== false) {
            // MySQL practically doesn't have this small limit; keep large value
            $maxVariables = 65535;
        }

        $computedMax = max(1, (int) floor($maxVariables / max(1, $numCols)));
        // Respect caller requested chunk size as an upper bound to limit memory
        $maxRowsPerInsert = min(max(1, $chunkSize), $computedMax);

        if ($total > $maxRowsPerInsert) {
            $chunks = array_chunk($rows, $maxRowsPerInsert);
            foreach ($chunks as $chunk) {
                try {
                    \Illuminate\Support\Facades\DB::table($table)->insert($chunk);
                    // free memory from the inserted chunk
                    unset($chunk);
                    if (function_exists('gc_collect_cycles')) gc_collect_cycles();
                } catch (\Throwable $e) {
                    // If an insert still fails, try inserting smaller pieces to make
                    // progress instead of aborting everything.
                    $subChunks = array_chunk($chunk, max(1, (int) floor($maxRowsPerInsert / 4)));
                    foreach ($subChunks as $sub) {
                        \Illuminate\Support\Facades\DB::table($table)->insert($sub);
                        if (function_exists('gc_collect_cycles')) gc_collect_cycles();
                    }
                }
            }
        } else {
            \Illuminate\Support\Facades\DB::table($table)->insert($rows);
            if (function_exists('gc_collect_cycles')) gc_collect_cycles();
        }
    }

    /**
     * Database-agnostic table truncation.
     * Handles MySQL and SQLite differently for foreign key constraints.
     */
    public static function truncateTable(string $tableName): void
    {
        $connection = \Illuminate\Support\Facades\DB::connection();
        $driver = $connection->getDriverName();
        
        try {
            if ($driver === 'sqlite') {
                // SQLite doesn't support TRUNCATE and doesn't have foreign key checks in the same way
                \Illuminate\Support\Facades\DB::statement("DELETE FROM {$tableName};");
            } else {
                // MySQL and others support foreign key checks
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                \Illuminate\Support\Facades\DB::table($tableName)->truncate();
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        } catch (\Exception $e) {
            // Fallback: try simple delete if truncation fails
            try {
                \Illuminate\Support\Facades\DB::table($tableName)->delete();
            } catch (\Exception $fallbackException) {
                throw new \Exception("Could not truncate table {$tableName}: " . $e->getMessage());
            }
        }
    }
}
