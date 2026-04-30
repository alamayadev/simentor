<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LaporanPerjalananDinasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                SeederUtils::truncateTable('laporan_perjalanan_dinas_dokumentasi');
                SeederUtils::truncateTable('laporan_perjalanan_dinas_detail');
                SeederUtils::truncateTable('laporan_perjalanan_dinas');
                if (isset($this->command)) $this->command->info('Truncated laporan_perjalanan_dinas tables');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate laporan_perjalanan_dinas tables: ' . $e->getMessage());
            }
        }

        // Path to the JSON file
        $jsonFilePath = database_path('initial_data/laporan_perjalanan_dinas.json');

        // Check if the file exists
        if (!File::exists($jsonFilePath)) {
            echo "JSON file not found: $jsonFilePath\n";
            return;
        }

        // Read the JSON file
        $json = File::get($jsonFilePath);
        $data = json_decode($json, true);

        // Check if JSON is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Invalid JSON in file: $jsonFilePath\n";
            return;
        }

        // Seed main table: laporan_perjalanan_dinas
        if (isset($data['laporan_perjalanan_dinas']) && is_array($data['laporan_perjalanan_dinas'])) {
            $rows = $this->prepareRows($data['laporan_perjalanan_dinas'], 'laporan_perjalanan_dinas');
            SeederUtils::insertInBatches('laporan_perjalanan_dinas', $rows);
            echo "Seeded " . count($rows) . " records into laporan_perjalanan_dinas table.\n";
        }

        // Seed detail table: laporan_perjalanan_dinas_detail
        if (isset($data['laporan_perjalanan_dinas_detail']) && is_array($data['laporan_perjalanan_dinas_detail'])) {
            $rows = $this->prepareRows($data['laporan_perjalanan_dinas_detail'], 'laporan_perjalanan_dinas_detail');
            SeederUtils::insertInBatches('laporan_perjalanan_dinas_detail', $rows);
            echo "Seeded " . count($rows) . " records into laporan_perjalanan_dinas_detail table.\n";
        }

        // Seed dokumentasi table: laporan_perjalanan_dinas_dokumentasi
        if (isset($data['laporan_perjalanan_dinas_dokumentasi']) && is_array($data['laporan_perjalanan_dinas_dokumentasi'])) {
            $rows = $this->prepareRows($data['laporan_perjalanan_dinas_dokumentasi'], 'laporan_perjalanan_dinas_dokumentasi');
            SeederUtils::insertInBatches('laporan_perjalanan_dinas_dokumentasi', $rows);
            echo "Seeded " . count($rows) . " records into laporan_perjalanan_dinas_dokumentasi table.\n";
        }
    }

    /**
     * Prepare rows for insertion.
     */
    private function prepareRows(array $items, string $table): array
    {
        $rows = [];
        foreach ($items as $item) {
            // Check if the item has a valid id
            if (!isset($item['id']) || empty($item['id'])) {
                echo "Skipping record without valid id in {$table}\n";
                continue;
            }

            if (!isset($item['created_at'])) $item['created_at'] = now();
            if (!isset($item['updated_at'])) $item['updated_at'] = now();

            // Normalize the row using SeederUtils, preserving the id field
            $normalizedRow = SeederUtils::normalizeRow($table, $item, true);

            $rows[] = $normalizedRow;
        }
        return $rows;
    }
}
