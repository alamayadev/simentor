<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Database\Seeders\SeederUtils;

class DataSurtugDetilTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('surat_tugas_detil')->truncate();
                if (isset($this->command)) $this->command->info('Truncated surat_tugas_detil');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate surat_tugas_detil: ' . $e->getMessage());
            }
        }

        // Path to the JSON file
        $jsonFilePath = database_path('initial_data/surat_tugas_detil.json');

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

            // Normalize the row using SeederUtils, preserving the id field
            $normalized = SeederUtils::normalizeRow('surat_tugas_detil', $row, true);
            if (empty($normalized)) continue;
            $rows[] = $normalized;
        }

        SeederUtils::insertInBatches('surat_tugas_detil', $rows);

        echo "Seeded " . count($rows) . " records into surat_tugas_detil table.\n";
    }
}
