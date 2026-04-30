<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PetaBsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table to remove any existing data
        DB::table('peta_bs')->truncate();

        // Read the CSV file
        $csvFile = database_path('initial_data/peta_bs.csv');
        $csvData = file_get_contents($csvFile);
        $lines = explode("\n", $csvData);

        // Get the header row
        $header = str_getcsv($lines[0], ';');

        // Process each data row
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue; // Skip empty lines
            }

            $row = str_getcsv($line, ';');

            // Skip rows that don't have the same number of columns as the header
            if (count($row) !== count($header)) {
                continue;
            }

            $data = [];
            for ($j = 0; $j < count($header); $j++) {
                $columnName = $header[$j];
                $value = $row[$j];

                // Clean the value (remove quotes if present)
                $value = trim($value, '"');

                $data[$columnName] = $value;
            }

            // Insert the data into the database
            DB::table('peta_bs')->insert($data);
        }
    }
}
