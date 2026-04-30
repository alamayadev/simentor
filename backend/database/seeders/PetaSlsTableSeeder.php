<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\SeederUtils;

class PetaSlsTableSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');

        $path = database_path('initial_data/peta_operator.csv');

        if (! file_exists($path)) {
            $this->command?->warn("CSV file not found: {$path}");
            return;
        }

        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            DB::table('peta_sls')->truncate();
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->command?->warn("Could not open CSV: {$path}");
            return;
        }

        $header = null;
        $batch = [];
        $batchSize = 200;
        $rowCount = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if ($header === null) {
                $header = $row;
                continue;
            }

            $data = array_combine($header, $row);
            if (! $data) {
                continue;
            }

            foreach ($data as $key => $value) {
                if ($value === '' || strtoupper((string) $value) === 'NULL') {
                    $data[$key] = null;
                }
            }

            $batch[] = [
                'kdkec' => $data['kdkec'] ?? null,
                'kddesa' => $data['kddesa'] ?? null,
                'nmkec' => $data['nmkec'] ?? null,
                'nmdesa' => $data['nmdesa'] ?? null,
                'filename' => $data['filename'] ?? null,
                'operator' => $data['operator'] ?? null,
                'jml' => $data['jml'] !== null ? (int) $data['jml'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $rowCount++;

            if (count($batch) >= $batchSize) {
                SeederUtils::insertInBatches('peta_sls', $batch, $batchSize);
                $this->command?->info("Inserted {$rowCount} rows...");
                $batch = [];
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }

        if (count($batch) > 0) {
            SeederUtils::insertInBatches('peta_sls', $batch, $batchSize);
            $this->command?->info("Inserted final batch, total rows: {$rowCount}");
        }

        fclose($handle);

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}
