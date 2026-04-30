<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove the truncation for now to avoid issues
        /*
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('settings')->truncate();
                if (isset($this->command)) $this->command->info('Truncated settings');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate settings: ' . $e->getMessage());
            }
        }
        */

        $jsonFilePath = database_path('initial_data/settings.json');

        if (!File::exists($jsonFilePath)) {
            echo "JSON file not found: $jsonFilePath\n";
            return;
        }

        $json = File::get($jsonFilePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Invalid JSON in file: $jsonFilePath\n";
            echo "JSON Error: " . json_last_error_msg() . "\n";
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
            $normalizedRow = SeederUtils::normalizeRow('settings', $row, true);

            $rows[] = $normalizedRow;
        }

        SeederUtils::insertInBatches('settings', $rows);

        echo "Seeded " . count($rows) . " records into settings table.\n";
    }
}
