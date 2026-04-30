<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use JsonMachine\Items;

class MitraKepkaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Increase memory limit and execution time
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);

        // Always truncate the table first to avoid primary key conflicts
        try {
            \Illuminate\Support\Facades\DB::table('mitra_kepka')->truncate();
            if (isset($this->command)) $this->command->info('Truncated mitra_kepka table');
        } catch (\Exception $e) {
            if (isset($this->command)) $this->command->warn('Could not truncate mitra_kepka: ' . $e->getMessage());
            // If truncate fails, try deleting all records
            try {
                \Illuminate\Support\Facades\DB::table('mitra_kepka')->delete();
                if (isset($this->command)) $this->command->info('Deleted all records from mitra_kepka table');
            } catch (\Exception $e2) {
                if (isset($this->command)) $this->command->warn('Could not delete from mitra_kepka: ' . $e2->getMessage());
            }
        }

        $jsonFilePath = database_path('initial_data/mitra_kepka.json');

        if (!File::exists($jsonFilePath)) {
            echo "JSON file not found: $jsonFilePath\n";
            return;
        }

        // Process the JSON file with streaming approach
        $this->processJsonFileStreaming($jsonFilePath);
    }

    private function processJsonFileStreaming($jsonFilePath): void
    {
        echo "Starting streaming JSON processing for Mitra Kepka...\n";

        // Use streaming JSON parser to avoid loading entire file into memory
        $stream = Items::fromFile($jsonFilePath);

        $processedRecords = 0;

        echo "Processing records one by one...\n";

        try {
            foreach ($stream as $item) {
                // Convert stdClass to array if needed
                $itemArray = is_array($item) ? $item : (array) $item;

                // Check if the item has a valid id
                if (!isset($itemArray['id']) || empty($itemArray['id'])) {
                    echo "Skipping record without valid id: " . json_encode($itemArray) . "\n";
                    continue;
                }

                // Normalize each item, preserving the id field
                $normalized = SeederUtils::normalizeRow('mitra_kepka', $itemArray, true);
                if (!empty($normalized)) {
                    // Insert one by one
                    try {
                        DB::table('mitra_kepka')->insert($normalized);
                        $processedRecords++;

                        // Show progress
                        if ($processedRecords % 100 == 0) {
                            echo "Processed $processedRecords records...\n";
                        }
                    } catch (\Exception $e) {
                        echo "Error inserting record with id {$itemArray['id']}: " . $e->getMessage() . "\n";
                        echo "Record data: " . json_encode($normalized) . "\n";
                    }
                }
            }

            echo "Successfully seeded $processedRecords records into mitra_kepka table.\n";

        } catch (\Exception $e) {
            echo "Error processing JSON file: " . $e->getMessage() . "\n";
            echo "Processed $processedRecords records before error.\n";
        }
    }
}
