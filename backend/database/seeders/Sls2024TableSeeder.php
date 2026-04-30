<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use JsonMachine\Items;

class Sls2024TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Increase memory limit and execution time
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);

        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('sls_2024')->truncate();
                if (isset($this->command)) $this->command->info('Truncated sls_2024');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate sls_2024: ' . $e->getMessage());
            }
        }

        // Path to the JSON file
        $jsonFilePath = database_path('initial_data/sls_2024.json');

        // Check if the file exists
        if (!File::exists($jsonFilePath)) {
            echo "JSON file not found: $jsonFilePath\n";
            return;
        }

        // Process the JSON file with streaming approach
        $this->processJsonFileStreaming($jsonFilePath);
    }

    private function processJsonFileStreaming($jsonFilePath): void
    {
        echo "Starting streaming JSON processing for SLS 2024...\n";

        // Use streaming JSON parser to avoid loading entire file into memory
        $stream = Items::fromFile($jsonFilePath);

        $batchSize = 50;
        $batchData = [];
        $processedRecords = 0;

        echo "Processing records in batches of $batchSize...\n";

        try {
            foreach ($stream as $item) {
                // Convert stdClass to array if needed
                $itemArray = is_array($item) ? $item : (array) $item;

                // Check if the item has a valid id
                if (!isset($itemArray['id']) || empty($itemArray['id'])) {
                    echo "Skipping record without valid id\n";
                    continue;
                }

                // Add timestamps
                $itemArray['created_at'] = now();
                $itemArray['updated_at'] = now();

                // Normalize each item, preserving the id field
                $normalized = SeederUtils::normalizeRow('sls_2024', $itemArray, true);
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

            echo "Successfully seeded $processedRecords records into sls_2024 table.\n";

        } catch (\Exception $e) {
            echo "Error processing JSON file: " . $e->getMessage() . "\n";
            echo "Processed $processedRecords records before error.\n";
        }
    }

    private function insertBatch(array $batchData): void
    {
        try {
            DB::table('sls_2024')->insert($batchData);
        } catch (\Exception $e) {
            echo "Error inserting batch: " . $e->getMessage() . "\n";
            // Try inserting one by one as fallback
            foreach ($batchData as $row) {
                try {
                    DB::table('sls_2024')->insert($row);
                } catch (\Exception $innerE) {
                    echo "Error inserting individual row: " . $innerE->getMessage() . "\n";
                }
            }
        }
    }
}
