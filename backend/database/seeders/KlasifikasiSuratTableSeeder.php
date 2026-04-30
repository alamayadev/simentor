<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class KlasifikasiSuratTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('klasifikasi_surat')->truncate();
                if (isset($this->command)) $this->command->info('Truncated klasifikasi_surat');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate klasifikasi_surat: ' . $e->getMessage());
            }
        }

        // Path to the JSON file
        $jsonFilePath = database_path('initial_data/klasifikasi_surat.json');

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

            if (!isset($row['created_at'])) $row['created_at'] = now();
            if (!isset($row['updated_at'])) $row['updated_at'] = now();

            // Normalize the row using SeederUtils, preserving the id field
            $normalizedRow = SeederUtils::normalizeRow('klasifikasi_surat', $row, true);

            $rows[] = $normalizedRow;
        }

        SeederUtils::insertInBatches('klasifikasi_surat', $rows);

        echo "Seeded " . count($rows) . " records into klasifikasi_surat table.\n";
    }
}
