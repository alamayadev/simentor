<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Sls2025Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use database-agnostic truncation
        SeederUtils::truncateTable('sls_2025');

        // Increase memory limit for large file
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);

        $csvFile = database_path('initial_data/sls_2025.csv');
        // Using fopen for memory efficiency on larger file
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            // Get header
            $headerLine = fgets($handle);
            $header = str_getcsv(trim($headerLine), ';');
            
            $batchData = [];
            $batchSize = 500;

            while (($line = fgets($handle)) !== FALSE) {
                $line = trim($line);
                if (empty($line)) continue;

                $row = str_getcsv($line, ';');
                if (count($row) !== count($header)) continue;

                $data = [];
                for ($j = 0; $j < count($header); $j++) {
                    $data[$header[$j]] = trim($row[$j], '"');
                }
                
                 // Add timestamps
                $data['created_at'] = now();
                $data['updated_at'] = now();

                $batchData[] = $data;

                if (count($batchData) >= $batchSize) {
                    DB::table('sls_2025')->insert($batchData);
                    $batchData = [];
                }
            }
            
            // Insert remaining
            if (!empty($batchData)) {
                DB::table('sls_2025')->insert($batchData);
            }
            
            fclose($handle);
        }
    }
}
