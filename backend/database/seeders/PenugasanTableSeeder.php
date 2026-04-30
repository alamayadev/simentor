<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PenugasanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Always truncate the table first to avoid primary key conflicts
        try {
            \Illuminate\Support\Facades\DB::table('penugasan')->truncate();
            if (isset($this->command)) $this->command->info('Truncated penugasan table');
        } catch (\Exception $e) {
            if (isset($this->command)) $this->command->warn('Could not truncate penugasan: ' . $e->getMessage());
            // If truncate fails, try deleting all records
            try {
                \Illuminate\Support\Facades\DB::table('penugasan')->delete();
                if (isset($this->command)) $this->command->info('Deleted all records from penugasan table');
            } catch (\Exception $e2) {
                if (isset($this->command)) $this->command->warn('Could not delete from penugasan: ' . $e2->getMessage());
            }
        }

        $jsonFilePath = database_path('initial_data/penugasan.json');

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
            $normalizedRow = SeederUtils::normalizeRow('penugasan', $row, true);

            $rows[] = $normalizedRow;
        }

        // Insert in smaller batches to avoid issues
        $chunks = array_chunk($rows, 100);
        $insertedCount = 0;
        foreach ($chunks as $chunk) {
            try {
                \Illuminate\Support\Facades\DB::table('penugasan')->insert($chunk);
                $insertedCount += count($chunk);
            } catch (\Exception $e) {
                echo "Error inserting chunk: " . $e->getMessage() . "\n";
                // Try inserting each row individually to identify the problematic one
                foreach ($chunk as $row) {
                    try {
                        \Illuminate\Support\Facades\DB::table('penugasan')->insert([$row]);
                        $insertedCount++;
                    } catch (\Exception $innerE) {
                        echo "Error inserting row: " . $innerE->getMessage() . "\n";
                        // Print the row that caused the issue
                        echo "Problematic row: " . json_encode($row) . "\n";
                    }
                }
            }
        }

        echo "Seeded " . $insertedCount . " records into penugasan table.\n";
    }
}
