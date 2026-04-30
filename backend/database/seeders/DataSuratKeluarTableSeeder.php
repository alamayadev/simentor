<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DataSuratKeluarTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('surat_keluar')->truncate();
                if (isset($this->command)) $this->command->info('Truncated surat_keluar');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate surat_keluar: ' . $e->getMessage());
            }
        }

        // Path to the JSON file
        $jsonFilePath = database_path('initial_data/surat_keluar.json');

        // Check if the file exists
        if (!File::exists($jsonFilePath)) {
            echo "JSON file not found: $jsonFilePath\n";
            return;
        }

        // Read the JSON file
        $json = File::get($jsonFilePath);
        $data = json_decode($json, true);

        // Check if JSON is valid
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

            // Convert tembusan array to JSON string if it exists
            if (isset($row['tembusan']) && is_array($row['tembusan'])) {
                $row['tembusan'] = json_encode($row['tembusan']);
            }

            if (!isset($row['created_at'])) $row['created_at'] = now();
            if (!isset($row['updated_at'])) $row['updated_at'] = now();

            // Normalize the row using SeederUtils, preserving the id field
            $normalizedRow = SeederUtils::normalizeRow('surat_keluar', $row, true);

            $rows[] = $normalizedRow;
        }

        SeederUtils::insertInBatches('surat_keluar', $rows);

        echo "Seeded " . count($rows) . " records into surat_keluar table.\n";
    }
}
