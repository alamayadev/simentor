<?php

namespace Database\Seeders;

use App\Models\AssetIT;
use App\Models\AssetITMaintenanceSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetITSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate tables using database-agnostic method
        SeederUtils::truncateTable('asset_it_maintenance_schedule');
        SeederUtils::truncateTable('asset_it');

        // Read the CSV data
        $csvPath = database_path('initial_data/asset-it.csv');
        $csvData = $this->parseCsv($csvPath);

        if (empty($csvData)) {
            return;
        }

        // Skip the header row
        $header = array_shift($csvData);

        // Clean the header keys to remove any invisible characters and BOM
        $header = array_map(function($item) {
            // Remove BOM and other invisible characters
            $cleaned = trim($item);
            // Remove any control characters
            $cleaned = preg_replace('/[[:cntrl:]]/', '', $cleaned);
            return $cleaned;
        }, $header);

        // Create a mapping of original CSV IDs and kode_asset to the database IDs
        $assetMap = [];
        $processedCount = 0;
        $skippedCount = 0;

        // Seed assets
        foreach ($csvData as $index => $row) {
            // Skip empty rows
            if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) {
                $skippedCount++;
                continue;
            }

            // Ensure the row has the same number of columns as the header
            if (count($row) !== count($header)) {
                // Pad the row with empty values if it's shorter than the header
                $row = array_pad($row, count($header), null);
            }

            // Clean the row values to remove any invisible characters
            $cleanRow = array_map(function($item) {
                return trim($item);
            }, $row);

            // Create an associative array from the CSV row using header as keys
            $assetData = array_combine($header, $cleanRow);

            // Since the 'id' is the first column (index 0), we can get it directly from the original row
            $idValue = $cleanRow[0] ?? null;

            // Verify that the required 'id' field exists
            if (!$idValue || empty($idValue)) {
                $skippedCount++;
                continue; // Skip rows without a valid ID
            }

            // Use the ID value we got directly from the row
            $assetData['id'] = $idValue;

            // Determine category based on asset type
            $category = $assetData['category'] ?? null;
            if (!$category && isset($assetData['device'])) {
                $category = $assetData['device'];
            }

            try {
                $asset = AssetIT::create([
                    'kode_asset' => $assetData['kode_asset'] ?? null,
                    'type' => $assetData['type'] ?? null,
                    'category' => $category,
                    'brand' => $assetData['brand'] ?? null,
                    'model' => $assetData['model'] ?? null,
                    'serial_number' => $assetData['serial_number'] ?? null,
                    'name' => $assetData['name'] ?? null,
                    'license_key' => $assetData['license_key'] ?? null,
                    'device' => $assetData['device'] ?? null,
                    'ip_address' => $assetData['ip_address'] ?? null,
                    'location' => $assetData['location'] ?? null,
                    'status' => $assetData['status'] ?? null,
                    'assigned_to' => $assetData['assigned_to'] ?? null,
                    'purchase_date' => $assetData['purchase_date'] ?? null,
                    'warranty_expiry' => $assetData['warranty_expiry'] ?? null,
                    'expiry_date' => $assetData['expiry_date'] ?? null,
                    'delivery_date' => $assetData['delivery_date'] ?? null,
                ]);

                $processedCount++;
            } catch (\Exception $e) {
                continue;
            }

            // Map both the original CSV ID and kode_asset to the database ID
            $assetMap[$assetData['id']] = $asset->id;
            $assetMap[$assetData['kode_asset']] = $asset->id;
        }

        // Note: If there are maintenance schedule entries, they would need to be handled separately
        // For now, we'll assume the CSV contains only asset data
    }

    /**
     * Parse CSV file and return array of rows
     */
    private function parseCsv($filePath): array
    {
        $data = [];
        $file = fopen($filePath, 'r');

        if ($file) {
            while (($row = fgetcsv($file, 0, ';')) !== FALSE) { // Using semicolon as delimiter
                // Ensure the row has the same number of elements as expected from the header
                // Add null for any missing elements
                $data[] = $row;
            }
            fclose($file);
        }

        return $data;
    }
}
