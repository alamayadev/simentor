<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Database\Seeders\SeederUtils;
use JsonMachine\Items;

class LandmarkTaggingWilkerstatTableSeeder extends Seeder
{
    public function run(): void
    {
        // Increase execution time for this process
        ini_set('max_execution_time', 0);

        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                DB::table('landmark_tagging_wilkerstat')->truncate();
                if (isset($this->command)) $this->command->info('Truncated landmark_tagging_wilkerstat');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate landmark_tagging_wilkerstat: ' . $e->getMessage());
            }
        }

        $jsonFilePath = database_path('initial_data/landmark_tagging_wilkerstat.json');

        if (!File::exists($jsonFilePath)) {
            echo "JSON file not found: $jsonFilePath\n";
            return;
        }

        // Process the JSON file with streaming approach
        $this->processJsonFileStreaming($jsonFilePath);
    }

    private function processJsonFileStreaming($jsonFilePath): void
    {
        echo "Starting streaming JSON processing...\n";

        // Use streaming JSON parser to avoid loading entire file into memory
        $stream = Items::fromFile($jsonFilePath);

        $batchSize = 50;
        $batchData = [];
        $processedRecords = 0;
        $totalRecords = 0;

        // First pass to count total records (optional, can be skipped for performance)
        echo "Processing records in batches of $batchSize...\n";

        try {
            foreach ($stream as $item) {
                $totalRecords++;

                // Convert stdClass to array if needed
                $itemArray = is_array($item) ? $item : (array) $item;

                // Check if the item has a valid id
                if (!isset($itemArray['id']) || empty($itemArray['id'])) {
                    echo "Skipping record without valid id\n";
                    continue;
                }

                // Normalize each item, preserving the id field
                $normalized = SeederUtils::normalizeRow('landmark_tagging_wilkerstat', $itemArray, true);
                if (!empty($normalized)) {
                    $batchData[] = $normalized;
                }

                // Insert batch when we reach batch size
                if (count($batchData) >= $batchSize) {
                    $this->insertBatch($batchData);
                    $processedRecords += count($batchData);

                    // Show progress
                    if ($processedRecords % (10 * $batchSize) == 0) {
                        echo "Processed $processedRecords records...\n";
                    }

                    // Clear batch data and free memory
                    $batchData = [];
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }
            }

            // Insert remaining records
            if (!empty($batchData)) {
                $this->insertBatch($batchData);
                $processedRecords += count($batchData);
            }

            echo "Successfully seeded $processedRecords records into landmark_tagging_wilkerstat table.\n";

        } catch (\Exception $e) {
            echo "Error processing JSON file: " . $e->getMessage() . "\n";
            echo "Processed $processedRecords records before error.\n";
        }
    }

    private function insertBatch(array $batchData): void
    {
        try {
            DB::table('landmark_tagging_wilkerstat')->insert($batchData);
        } catch (\Exception $e) {
            echo "Error inserting batch: " . $e->getMessage() . "\n";
            // Try inserting one by one as fallback
            foreach ($batchData as $row) {
                try {
                    DB::table('landmark_tagging_wilkerstat')->insert($row);
                } catch (\Exception $innerE) {
                    echo "Error inserting individual row: " . $innerE->getMessage() . "\n";
                }
            }
        }
    }
}
