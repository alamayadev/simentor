<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Database\Seeders\SeederUtils;

class TiketsTableSeeder extends Seeder
{
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                DB::table('tikets')->truncate();
                if (isset($this->command)) $this->command->info('Truncated tikets');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate tikets: ' . $e->getMessage());
            }
        }

        $jsonFilePath = database_path('initial_data/tikets.json');

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

            // ensure required non-nullable fields are present
            if (!isset($row['status'])) {
                $row['status'] = 'Pending';
            }
            if (!isset($row['created_at'])) $row['created_at'] = now();
            if (!isset($row['updated_at'])) $row['updated_at'] = now();

            // Normalize the row using SeederUtils, preserving the id field
            $normalizedRow = SeederUtils::normalizeRow('tikets', $row, true);

            $rows[] = $normalizedRow;
        }

        SeederUtils::insertInBatches('tikets', $rows);

        echo "Seeded " . count($rows) . " records into tikets table.\n";
    }
}
