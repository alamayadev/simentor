<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TargetPeta;

class TargetPetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the CSV file
        $path = database_path('initial_data/peta_operator_all.csv');

        // Check if file exists
        if (!file_exists($path)) {
            $this->command->warn("CSV file not found: {$path}");
            return;
        }

        // Open the file
        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->command->warn("Could not open CSV: {$path}");
            return;
        }

        // Get the header
        $header = fgetcsv($handle, 0, ';');

        // Find the indices of the columns we need
        $filenameIndex = array_search('filename', $header);
        $keteranganIndex = array_search('keterangan', $header);

        // Check if required columns exist
        if ($filenameIndex === false) {
            $this->command->error("Required 'filename' column not found in CSV");
            fclose($handle);
            return;
        }

        // Batch insert for better performance
        $batch = [];
        $batchSize = 200;
        $rowCount = 0;

        // Process each row
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Extract only the filename and keterangan columns
            $filename = $row[$filenameIndex] ?? null;
            $keterangan = ($keteranganIndex !== false) ? ($row[$keteranganIndex] ?? null) : null;

            // Skip rows without filename
            if (empty($filename)) {
                continue;
            }

            $batch[] = [
                'filename' => $filename,
                'keterangan' => $keterangan,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $rowCount++;

            // Insert batch when it reaches the batch size
            if (count($batch) >= $batchSize) {
                TargetPeta::insert($batch);
                $this->command->info("Inserted {$rowCount} rows...");
                $batch = [];
            }
        }

        // Insert remaining records
        if (count($batch) > 0) {
            TargetPeta::insert($batch);
            $this->command->info("Inserted final batch, total rows: {$rowCount}");
        }

        fclose($handle);
    }
}
