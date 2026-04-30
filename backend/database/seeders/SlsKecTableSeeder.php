<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Database\Seeders\SeederUtils;

class SlsKecTableSeeder extends Seeder
{
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                DB::table('sls_kecs')->truncate();
                if (isset($this->command)) $this->command->info('Truncated sls_kecs');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate sls_kecs: ' . $e->getMessage());
            }
        }

        $jsonFilePath = database_path('initial_data/sls_kec.json');

        if (!File::exists($jsonFilePath)) {
            echo "JSON file not found: $jsonFilePath\n";
            return;
        }

        $json = File::get($jsonFilePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Invalid JSON in file: $jsonFilePath\n";
            return;
        }

        $rows = [];
        foreach ($data as $row) {
            // Check if the item has a valid id
            if (!isset($row['id']) || empty($row['id'])) {
                echo "Skipping record without valid id\n";
                continue;
            }

            // Normalize the row using SeederUtils, preserving the id field
            $normalized = SeederUtils::normalizeRow('sls_kecs', $row, true);
            if (empty($normalized)) continue;
            $rows[] = $normalized;
        }

        SeederUtils::insertInBatches('sls_kecs', $rows);

        echo "Seeded " . count($rows) . " records into sls_kecs table.\n";
    }
}
