<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use database-agnostic truncation
        SeederUtils::truncateTable('kecamatans');

        $csvFile = database_path('initial_data/kecamatan.csv');
        $csvData = file_get_contents($csvFile);
        $lines = explode("\n", $csvData);
        $header = str_getcsv($lines[0], ';');

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
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

            DB::table('kecamatans')->insert($data);
        }
    }
}
