<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Alokasi;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class AlokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('alokasis')->truncate();
                if (isset($this->command)) $this->command->info('Truncated alokasis');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate alokasis: ' . $e->getMessage());
            }
        }
        // Path to the text file
    $filePath = database_path('initial_data/alokasi.txt');

        // Check if file exists
        if (!File::exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        // Read the file content
        $content = File::get($filePath);

        // Split content into lines
        $lines = explode("\n", $content);

        // Remove the header line
        array_shift($lines);

        $successCount = 0;
        $errorCount = 0;
        $duplicateCount = 0;
        $errors = [];

        $batch = [];
        $batchSize = 500;

        // Stream the input file line-by-line to avoid loading whole file in memory
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->command->error('Could not open file: ' . $filePath);
            return;
        }

        // Read header
        $header = fgets($handle);
        $lineNum = 1; // header counted as line 1

        while (!feof($handle)) {
            $line = fgets($handle);
            $lineNum++;

            if ($line === false) {
                continue;
            }

            if (empty(trim($line))) {
                continue;
            }

            $data = str_getcsv($line, ';', '"', '\\');
            if (count($data) < 19) {
                $errorCount++;
                if (count($errors) < 10) {
                    $errors[] = "Line " . $lineNum . ": Insufficient fields (" . count($data) . ")";
                }
                continue;
            }

            $idsls = trim($data[1], "\" \t\n");
            if ($idsls === '') {
                $errorCount++;
                if (count($errors) < 10) {
                    $errors[] = "Line " . $lineNum . ": empty idsls";
                }
                continue;
            }

            $row = [
                'idsls' => $idsls,
                'kdprov' => trim($data[2], "\" \t\n"),
                'kdkab' => trim($data[3], "\" \t\n"),
                'kdkec' => trim($data[4], "\" \t\n"),
                'kddesa' => trim($data[5], "\" \t\n"),
                'kdsls' => trim($data[6], "\" \t\n"),
                'nmprov' => trim($data[7], "\" \t\n"),
                'nmkab' => trim($data[8], "\" \t\n"),
                'nmkec' => trim($data[9], "\" \t\n"),
                'nmdesa' => trim($data[10], "\" \t\n"),
                'nmsls' => trim($data[11], "\" \t\n"),
                'periode' => trim($data[12], "\" \t\n"),
                'idsubsls' => trim($data[13], "\" \t\n"),
                'iddesa' => trim($data[14], "\" \t\n"),
                'alokasi_scan' => trim($data[15], "\" \t\n"),
                'alokasi_georef' => trim($data[16], "\" \t\n"),
                'alokasi_geojson' => trim($data[17], "\" \t\n"),
                'alokasi_muatan' => trim($data[18], "\" \t\n"),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $batch[] = $row;

            if (count($batch) >= $batchSize) {
                // DB-level deduplication for the batch
                try {
                    // Remove duplicate idsls inside this batch (keep first occurrence)
                    $seen = [];
                    $uniqueBatch = [];
                    foreach ($batch as $r) {
                        if (!isset($seen[$r['idsls']])) {
                            $seen[$r['idsls']] = true;
                            $uniqueBatch[] = $r;
                        } else {
                            $duplicateCount++;
                        }
                    }

                    $ids = array_map(function ($r) { return $r['idsls']; }, $uniqueBatch);
                    $existing = [];
                    if (DB::getSchemaBuilder()->hasTable('alokasis') && !empty($ids)) {
                        $existing = DB::table('alokasis')->whereIn('idsls', $ids)->pluck('idsls')->all();
                    }

                    $existingMap = array_fill_keys($existing, true);
                    $toInsert = array_values(array_filter($uniqueBatch, function ($r) use ($existingMap) {
                        return !isset($existingMap[$r['idsls']]);
                    }));

                    // duplicateCount already incremented for intra-batch duplicates
                    $duplicateCount += (count($uniqueBatch) - count($toInsert));

                    if (!empty($toInsert)) {
                        DB::table('alokasis')->insert($toInsert);
                        $successCount += count($toInsert);
                    }
                } catch (\Exception $e) {
                    // Fallback: attempt per-row insert to salvage non-conflicting rows
                    foreach ($uniqueBatch as $r) {
                        try {
                            DB::table('alokasis')->insert($r);
                            $successCount++;
                        } catch (\Exception $e2) {
                            $errorCount++;
                            if (count($errors) < 10) {
                                $errors[] = "Row insert error at approx line " . $lineNum . ": " . $e2->getMessage();
                            }
                        }
                    }
                }

                $batch = [];
            }

            if ((($successCount + $errorCount + $duplicateCount) % 500) == 0) {
                $this->command->info("Processed " . ($successCount + $errorCount + $duplicateCount) . " records...");
            }
        }

        // Final batch: same deduplication logic
        if (!empty($batch)) {
            try {
                // Deduplicate final batch as well (keep first occurrence)
                $seen = [];
                $uniqueBatch = [];
                foreach ($batch as $r) {
                    if (!isset($seen[$r['idsls']])) {
                        $seen[$r['idsls']] = true;
                        $uniqueBatch[] = $r;
                    } else {
                        $duplicateCount++;
                    }
                }

                $ids = array_map(function ($r) { return $r['idsls']; }, $uniqueBatch);
                $existing = [];
                if (DB::getSchemaBuilder()->hasTable('alokasis') && !empty($ids)) {
                    $existing = DB::table('alokasis')->whereIn('idsls', $ids)->pluck('idsls')->all();
                }
                $existingMap = array_fill_keys($existing, true);
                $toInsert = array_values(array_filter($uniqueBatch, function ($r) use ($existingMap) {
                    return !isset($existingMap[$r['idsls']]);
                }));

                $duplicateCount += (count($uniqueBatch) - count($toInsert));
                if (!empty($toInsert)) {
                    DB::table('alokasis')->insert($toInsert);
                    $successCount += count($toInsert);
                }
            } catch (\Exception $e) {
                // Fallback: attempt per-row insert to salvage non-conflicting rows
                foreach ($uniqueBatch as $r) {
                    try {
                        DB::table('alokasis')->insert($r);
                        $successCount++;
                    } catch (\Exception $e2) {
                        $errorCount++;
                        if (count($errors) < 10) {
                            $errors[] = "Final row insert error: " . $e2->getMessage();
                        }
                    }
                }
            }
        }

        fclose($handle);

        $this->command->info("Alokasi data seeding completed.");
        $this->command->info("Successfully imported: $successCount records");
        $this->command->info("Failed to import: $errorCount records");

        if (!empty($errors)) {
            $this->command->warn("First few errors:");
            foreach ($errors as $error) {
                $this->command->line($error);
            }
        }
    }
}
