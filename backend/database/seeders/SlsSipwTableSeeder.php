<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Database\Seeders\SeederUtils;

class SlsSipwTableSeeder extends Seeder
{
    public function run(): void
    {
        // Increase memory limit and execution time
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);

    $path = database_path('initial_data/sls_sipw.csv');

        if (! file_exists($path)) {
            $this->command?->warn("CSV file not found: {$path}");
            return;
        }

        // Optionally truncate in local/testing when enabled
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            DB::table('sls_sipw')->truncate();
        }

        // Use native PHP fgetcsv to avoid new composer deps at runtime
        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->command?->warn("Could not open CSV: {$path}");
            return;
        }

        $header = null;
        $batch = [];
        $batchSize = 200; // Reduced from 500 to be more conservative
        $rowCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = $row;
                continue;
            }

            $data = array_combine($header, $row);
            if (! $data) {
                continue;
            }

            // Normalize empty strings to null for numeric fields
            foreach ($data as $k => $v) {
                if ($v === '') {
                    $data[$k] = null;
                }
            }

            if (isset($data['status_sls']) && strtoupper($data['status_sls']) === 'NULL') {
                $data['status_sls'] = null;
            }

            // Map CSV column names to DB columns (they match mostly)
            $batch[] = [
                'idfrs' => $data['idfrs'] ?? null,
                'idsls' => $data['idsls'] ?? null,
                // Keep administrative codes as strings to preserve leading zeros
                'kdprov' => $data['kdprov'] ?? null,
                'kdkab' => $data['kdkab'] ?? null,
                'kdkec' => $data['kdkec'] ?? null,
                'kddesa' => $data['kddesa'] ?? null,
                'kdsls' => $data['kdsls'] ?? null,
                'klas' => $data['klas'] !== null ? (int)$data['klas'] : null,
                'nmprov' => $data['nmprov'] ?? null,
                'nmkab' => $data['nmkab'] ?? null,
                'nmkec' => $data['nmkec'] ?? null,
                'nmdesa' => $data['nmdesa'] ?? null,
                'nama_sls' => $data['nama_sls'] ?? null,
                'jenis_sls' => $data['jenis_sls'] ?? null,
                'ketua_sls' => $data['ketua_sls'] ?? null,
                'j_subsls' => $data['j_subsls'] !== null ? (int) $data['j_subsls'] : null,
                'muatan_dominan' => $data['muatan_dominan'] ?? null,
                'flag_perubahan_sls' => $data['flag_perubahan_sls'] !== null ? (int)$data['flag_perubahan_sls'] : null,
                'status_olah_peta' => $data['status_olah_peta'] ?? null,
                'shapes_comparation' => $data['shapes_comparation'] ?? null,
                'location' => $data['location'] ?? null,
                'status_sls' => $data['status_sls'] ?? null,
                'peta_banding_rs' => $data['peta_banding_rs'] ?? null,
                'operator' => $data['operator'] ?? null,
                // Keep existing fields that may have been added by other processes
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $rowCount++;

            if (count($batch) >= $batchSize) {
                // Use our driver-aware batched inserter to avoid hitting bind-variable
                // limits and keep memory usage stable.
                SeederUtils::insertInBatches('sls_sipw', $batch, $batchSize);
                $this->command?->info("Inserted {$rowCount} rows...");

                // Free memory aggressively
                $batch = [];
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }

        if (count($batch) > 0) {
            SeederUtils::insertInBatches('sls_sipw', $batch, $batchSize);
            $this->command?->info("Inserted final batch, total rows: {$rowCount}");

            // Free memory
            $batch = [];
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        fclose($handle);

        // Final garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}
