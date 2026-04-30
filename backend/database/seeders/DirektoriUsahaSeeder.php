<?php

namespace Database\Seeders;

use App\Models\DirektoriUsaha;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DirektoriUsahaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable query log to save memory
        DB::connection()->disableQueryLog();
        
        $csvFile = database_path('initial_data/direktori_usaha.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $this->command->info('╔═══════════════════════════════════════════════════════════════╗');
        $this->command->info('║         DIREKTORI USAHA DATA IMPORT STARTED                   ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════════╝');
        $this->command->newLine();
        
        $fileSize = round(filesize($csvFile) / 1024 / 1024, 2);
        $this->command->info("📁 File size: {$fileSize} MB");
        $this->command->info("💾 Memory limit: " . ini_get('memory_limit'));
        $this->command->newLine();

        // Truncate table before import
        $this->command->info("🗑️  Truncating existing data...");
        DB::statement('TRUNCATE TABLE direktori_usaha');
        $this->command->info("✓ Table cleared");
        $this->command->newLine();

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle); // Skip header row

        $batchSize = 250; // Further reduced for memory efficiency
        $totalRows = 0;
        $batchCount = 0;
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $this->command->info("🚀 Starting import with batch size: {$batchSize}");
        $this->command->getOutput()->progressStart(430716);

        // Prepare PDO statement for raw inserts
        $pdo = DB::connection()->getPdo();
        $columns = 'idsbr,nama_usaha,alamat_usaha,kode_wilayah,kdprov,kdkab,kdkec,kddesa,nmprov,nmkab,nmkec,nmdesa,perusahaan_id,status_perusahaan,skor_kalo,kegiatan_usaha,rank_nama,rank_alamat,history_ref_profiling_id,skala_usaha,sumber_data,latitude,longitude,latlong_status,gcid,gcs_result,allow_cancel,allow_edit,allow_flagging,latitude_gc,longitude_gc,latlong_status_gc,gc_username,nama_usaha_gc,alamat_usaha_gc,name_similarity,created_at,updated_at';
        $placeholders = '(' . str_repeat('?,', 37) . '?)';
        
        $values = [];
        $params = [];

        while (($row = fgetcsv($handle)) !== false) {
            // Prepare values for this row
            $rowData = [
                $row[0] ?? null,
                $row[1] ?? null,
                $row[2] ?? null,
                !empty($row[3]) ? (string) $row[3] : null,
                !empty($row[4]) ? (string) $row[4] : null,
                !empty($row[5]) ? (string) $row[5] : null,
                !empty($row[6]) ? str_pad((string) $row[6], 3, '0', STR_PAD_LEFT) : null,
                !empty($row[7]) ? str_pad((string) $row[7], 3, '0', STR_PAD_LEFT) : null,
                $row[8] ?? null,
                $row[9] ?? null,
                $row[10] ?? null,
                $row[11] ?? null,
                $row[12] ?? null,
                $row[13] ?? null,
                $row[14] ?? null,
                $row[15] ?? null,
                $row[16] ?? null,
                $row[17] ?? null,
                !empty($row[18]) ? $row[18] : null,
                $row[19] ?? null,
                $row[20] ?? null,
                !empty($row[21]) ? (string) $row[21] : null,
                !empty($row[22]) ? (string) $row[22] : null,
                $row[23] ?? null,
                $row[24] ?? null,
                !empty($row[25]) && is_numeric($row[25]) ? (int) $row[25] : null,
                ($row[26] ?? '') === 'True' || ($row[26] ?? '') === 'true' || ($row[26] ?? '') === '1' ? 1 : 0,
                ($row[27] ?? '') === 'True' || ($row[27] ?? '') === 'true' || ($row[27] ?? '') === '1' ? 1 : 0,
                ($row[28] ?? '') === 'True' || ($row[28] ?? '') === 'true' || ($row[28] ?? '') === '1' ? 1 : 0,
                !empty($row[29]) ? (string) $row[29] : null,
                !empty($row[30]) ? (string) $row[30] : null,
                $row[31] ?? null,
                $row[32] ?? null,
                $row[33] ?? null,
                $row[34] ?? null,
                !empty($row[35]) && is_numeric($row[35]) ? $row[35] : null,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
            ];

            $values[] = $placeholders;
            $params = array_merge($params, $rowData);

            if (count($values) >= $batchSize) {
                // Execute batch insert
                $sql = "INSERT INTO direktori_usaha ({$columns}) VALUES " . implode(',', $values);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                $totalRows += count($values);
                $batchCount++;
                $this->command->getOutput()->progressAdvance(count($values));
                
                // Clear arrays
                unset($values, $params, $stmt);
                $values = [];
                $params = [];
                
                // Force garbage collection every 50 batches
                if ($batchCount % 50 === 0) {
                    gc_collect_cycles();
                    
                    $currentMemory = memory_get_usage(true);
                    $memoryUsed = round($currentMemory / 1024 / 1024, 2);
                    $this->command->getOutput()->write(" [Mem: {$memoryUsed}MB]");
                }
            }
        }

        // Insert remaining records
        if (!empty($values)) {
            $sql = "INSERT INTO direktori_usaha ({$columns}) VALUES " . implode(',', $values);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $totalRows += count($values);
            $batchCount++;
            $this->command->getOutput()->progressAdvance(count($values));
            unset($values, $params, $stmt);
        }

        fclose($handle);
        $this->command->getOutput()->progressFinish();

        $duration = round(microtime(true) - $startTime, 2);
        $memoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        
        $this->command->newLine();
        $this->command->info('╔═══════════════════════════════════════════════════════════════╗');
        $this->command->info('║                  IMPORT COMPLETED ✓                           ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════════╝');
        $this->command->newLine();
        $this->command->info("📊 Statistics:");
        $this->command->info("   • Total rows inserted: " . number_format($totalRows));
        $this->command->info("   • Total batches: " . number_format($batchCount));
        $this->command->info("   • Time taken: {$duration} seconds");
        $this->command->info("   • Average speed: " . number_format(round($totalRows / $duration, 2)) . " rows/second");
        $this->command->info("   • Peak memory usage: {$memoryPeak} MB");
        $this->command->newLine();
    }
}
