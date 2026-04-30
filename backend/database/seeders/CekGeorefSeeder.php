<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class CekGeorefSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temporarily increase memory limit for processing large CSV files
        $originalMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 0);
        echo "Memory limit temporarily increased from {$originalMemoryLimit} to 1G\n";

        // Always truncate the table before seeding
        try {
            \Illuminate\Support\Facades\DB::table('cek_georefs')->truncate();
            echo "Truncated cek_georefs table\n";
        } catch (\Exception $e) {
            echo "Could not truncate cek_georefs: " . $e->getMessage() . "\n";
        }

        $filePath = database_path('initial_data/olah_georef.csv');

        if (!File::exists($filePath)) {
            echo "File not found: {$filePath}\n";
            // Restore original memory limit
            ini_set('memory_limit', $originalMemoryLimit);
            return;
        }

        echo "Starting to import data from: {$filePath}\n";

        // Process file in chunks to avoid memory issues
        $this->processCsvInChunks($filePath);

        // Restore original memory limit
        ini_set('memory_limit', $originalMemoryLimit);
        echo "Memory limit restored to: {$originalMemoryLimit}\n";
    }

    private function processCsvInChunks($filePath, $chunkSize = 25)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            echo "Could not open file: {$filePath}\n";
            return;
        }

        // Skip header line
        $header = fgetcsv($handle, 0, ';');

        $batchData = [];
        $processedCount = 0;
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $lineNum = 1; // Start at 1 because we skipped the header

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $lineNum++;

            // CSV may contain optional columns; require at least kec, desa, filename, and fullpath
            if (count($data) < 5) {
                $errorCount++;
                if (count($errors) < 10) $errors[] = "Line " . ($lineNum) . ": Insufficient fields (" . count($data) . ")";
                continue;
            }

            try {
                // Prepare batch data - map columns by header order, falling back to null/defaults
                $createdTime = isset($data[5]) ? trim($data[5], '"') : null;
                if ($createdTime !== null && $createdTime !== '') {
                    try {
                        $createdTime = Carbon::parse($createdTime);
                    } catch (\Throwable $e) {
                        $createdTime = null;
                    }
                } else {
                    $createdTime = null;
                }

                $recordData = [
                    'kec' => isset($data[0]) ? trim($data[0], '"') : null,
                    'desa' => isset($data[1]) ? trim($data[1], '"') : null,
                    'level_3' => isset($data[2]) ? trim($data[2], '"') : null,
                    'filename' => isset($data[3]) ? trim($data[3], '"') : null,
                    'fullpath' => isset($data[4]) ? trim($data[4], '"') : null,
                    'created_time' => $createdTime,
                    'jenis' => isset($data[6]) ? trim($data[6], '"') : null,
                    'lokasi' => isset($data[7]) ? trim($data[7], '"') : null,
                    'kodename' => isset($data[8]) ? trim($data[8], '"') : 'N/A',
                    'kode' => isset($data[9]) ? trim($data[9], '"') : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $batchData[] = $recordData;
                $processedCount++;

                // Insert batch when we reach batch size
                if (count($batchData) >= $chunkSize) {
                    \Illuminate\Support\Facades\DB::table('cek_georefs')->insert($batchData);
                    $successCount += count($batchData);

                    // Free memory aggressively
                    $batchData = []; // Reset batch
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }

                    // Show progress every 20 batches
                    if ($processedCount % ($chunkSize * 20) == 0) {
                        echo "✓ Processed: {$processedCount} records | Success: {$successCount}\n";
                    }
                }

            } catch (\Exception $e) {
                $errorCount++;
                if (count($errors) < 10) $errors[] = "Line " . ($lineNum) . ": " . $e->getMessage();
                // Continue processing other records
            }
        }

        // Insert any remaining data
        if (!empty($batchData)) {
            try {
                \Illuminate\Support\Facades\DB::table('cek_georefs')->insert($batchData);
                $successCount += count($batchData);
                echo "✓ Final batch inserted: " . count($batchData) . " records\n";

                // Free memory
                $batchData = [];
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            } catch (\Exception $e) {
                $errorCount += count($batchData);
                echo "✗ Failed to insert final batch: " . $e->getMessage() . "\n";
            }
        }

        fclose($handle);

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🎉 CekGeoref data seeding completed!\n";
        echo "📊 SUMMARY:\n";
        echo "   • Total records processed: " . ($successCount + $errorCount) . "\n";
        echo "   • ✅ Successfully imported: {$successCount} records\n";
        echo "   • ❌ Failed to import: {$errorCount} records\n";

        if (!empty($errors)) {
            echo "\n⚠️  First few errors:\n";
            foreach ($errors as $error) {
                echo "   • {$error}\n";
            }
        }

        echo str_repeat("=", 60) . "\n";

        // Final record count verification
        $finalCount = \Illuminate\Support\Facades\DB::table('cek_georefs')->count();
        echo "🔍 Final verification: {$finalCount} records now in database\n";
    }
}
