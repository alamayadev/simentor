<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CekScan;
use Illuminate\Support\Facades\File;
use Database\Seeders\SeederUtils;

class CekScanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Always truncate the table before seeding
        try {
            \Illuminate\Support\Facades\DB::table('cek_scans')->truncate();
            echo "Truncated cek_scans table\n";
        } catch (\Exception $e) {
            echo "Could not truncate cek_scans: " . $e->getMessage() . "\n";
        }
        // Path to the text file
    $filePath = database_path('initial_data/olah_scan.csv');

        // Check if file exists
        if (!File::exists($filePath)) {
            echo "File not found: {$filePath}\n";
            return;
        }

        echo "Starting to import data from: {$filePath}\n";

        // Stream the file line-by-line to avoid loading entire content into memory.
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            echo "Could not open file: {$filePath}\n";
            return;
        }

        // First pass: count non-empty lines to report progress (lightweight streaming)
        $totalLines = 0;
        while (!feof($handle)) {
            $ln = fgets($handle);
            if ($ln !== false && trim($ln) !== '') $totalLines++;
        }

        // Rewind and skip header
        rewind($handle);
        $headerLine = fgets($handle);

        echo "Total records to process: {$totalLines}\n";

        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $batchData = [];
    // Use a small batch size to keep memory low under default PHP limits.
    // We also pass the same value to insertInBatches so it limits rows per INSERT.
    $batchSize = 25; // Process 25 records at a time
        $processedCount = 0;

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;
            if (trim($line) === '') continue;

            $data = str_getcsv($line, ';', '"', '\\');

            // Expect at least 9 fields
            if (count($data) < 9) {
                $errorCount++;
                if (count($errors) < 10) {
                    $errors[] = "Line " . ($processedCount + 2) . ": Insufficient fields (" . count($data) . ")";
                }
                continue;
            }

            try {
                // use plain datetime strings (avoid heavy Carbon allocations per row)
                $now = date('Y-m-d H:i:s');

                $recordData = [
                    'kec' => isset($data[0]) ? trim($data[0], '"') : null,
                    'desa' => isset($data[1]) ? trim($data[1], '"') : null,
                    'filename' => isset($data[2]) ? trim($data[2], '"') : null,
                    'fullpath' => isset($data[3]) ? trim($data[3], '"') : null,
                    'created_time' => isset($data[4]) ? trim($data[4], '"') : null,
                    'jenis' => isset($data[5]) ? trim($data[5], '"') : null,
                    'lokasi' => isset($data[6]) ? trim($data[6], '"') : null,
                    'kodename' => isset($data[7]) ? trim($data[7], '"') : null,
                    'kode' => isset($data[8]) ? trim($data[8], '"') : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $batchData[] = $recordData;
                $processedCount++;

                if (count($batchData) >= $batchSize || $processedCount == $totalLines) {
                    SeederUtils::insertInBatches('cek_scans', $batchData, $batchSize);
                    $inserted = count($batchData);
                    $successCount += $inserted;

                    // free memory ASAP
                    $batchData = null;
                    if (function_exists('gc_collect_cycles')) gc_collect_cycles();
                    $batchData = [];

                    $percentage = $totalLines > 0 ? round(($processedCount / $totalLines) * 100, 1) : 100;
                    echo "✓ Batch inserted: " . count($batchData) . " records | Progress: {$processedCount}/{$totalLines} ({$percentage}%) | Success: {$successCount}\n";

                    $batchData = [];
                }
            } catch (\Exception $e) {
                $errorCount++;
                if (count($errors) < 10) {
                    $errors[] = "Line " . ($processedCount + 2) . ": " . $e->getMessage();
                }
                continue;
            }
        }

        // Insert any remaining records in the last partial batch
        if (!empty($batchData)) {
            SeederUtils::insertInBatches('cek_scans', $batchData, $batchSize);
            $successCount += count($batchData);
            $percentage = $totalLines > 0 ? round(($processedCount / $totalLines) * 100, 1) : 100;
            echo "✓ Batch inserted: " . count($batchData) . " records | Progress: {$processedCount}/{$totalLines} ({$percentage}%) | Success: {$successCount}\n";
            // free memory
            $batchData = null;
            if (function_exists('gc_collect_cycles')) gc_collect_cycles();
            $batchData = [];
        }

        fclose($handle);

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🎉 CekScan data seeding completed!\n";
        echo "📊 SUMMARY:\n";
        echo "   • Total records processed: " . ($successCount + $errorCount) . "\n";
        echo "   • ✅ Successfully imported: {$successCount} records\n";
        echo "   • ❌ Failed to import: {$errorCount} records\n";

        if ($successCount > 0) {
            $successRate = round(($successCount / ($successCount + $errorCount)) * 100, 1);
            echo "   • 📈 Success rate: {$successRate}%\n";
        }

        if (!empty($errors)) {
            echo "\n⚠️  First few errors:\n";
            foreach ($errors as $error) {
                echo "   • {$error}\n";
            }
        }

        echo str_repeat("=", 60) . "\n";

        // Final record count verification
        $finalCount = \Illuminate\Support\Facades\DB::table('cek_scans')->count();
        echo "🔍 Final verification: {$finalCount} records now in database\n";
    }
}
