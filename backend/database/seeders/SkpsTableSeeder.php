<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SkpsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('skps')->truncate();
                if (isset($this->command)) $this->command->info('Truncated skps');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate skps: ' . $e->getMessage());
            }
        }

        $jsonFilePath = database_path('initial_data/skps.json');

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

            if (!isset($row['created_at'])) $row['created_at'] = now();
            if (!isset($row['updated_at'])) $row['updated_at'] = now();

            // Normalize the row using SeederUtils, preserving the id field
            $normalizedRow = SeederUtils::normalizeRow('skps', $row, true);

            $rows[] = $normalizedRow;
        }

        SeederUtils::insertInBatches('skps', $rows);

        echo "Seeded " . count($rows) . " records into skps table.\n";
    }
}
