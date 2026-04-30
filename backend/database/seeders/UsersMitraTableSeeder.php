<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use JsonMachine\Items;

class UsersMitraTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Increase memory limit and execution time
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 0);
        
        echo "Starting UsersMitraTableSeeder...\n";

        try {
            // Path to the JSON file
            $jsonFilePath = database_path('initial_data/mitra_kepka.json');

            // Check if the file exists
            if (!File::exists($jsonFilePath)) {
                echo "JSON file not found: $jsonFilePath\n";
                return;
            }

            // Process the JSON file with streaming approach
            $this->processJsonFileStreaming($jsonFilePath);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            echo "File: " . $e->getFile() . "\n";
        }

        echo "UsersMitraTableSeeder completed.\n";
    }
    
    private function processJsonFileStreaming($jsonFilePath): void
    {
        echo "Starting streaming JSON processing for Users Mitra...\n";
        
        // Use streaming JSON parser to avoid loading entire file into memory
        $stream = Items::fromFile($jsonFilePath);
        
        $batchSize = 50;
        $batchData = [];
        $processedRecords = 0;
        $insertedRecords = 0;
        
        // Get existing emails to avoid duplicates (in batches for memory efficiency)
        echo "Fetching existing emails...\n";
        $existingEmails = [];
        $emailQuery = DB::table('users')->select('email')->orderBy('id');
        foreach ($emailQuery->lazy(1000) as $user) {
            $existingEmails[strtolower($user->email)] = true;
        }
        
        echo "Found " . count($existingEmails) . " existing emails.\n";
        echo "Processing records in batches of $batchSize...\n";
        
        try {
            foreach ($stream as $item) {
                // Convert stdClass to array if needed
                $itemArray = is_array($item) ? $item : (array) $item;
                
                $email = strtolower($itemArray['email'] ?? '');
                if (empty($email) || isset($existingEmails[$email])) {
                    $processedRecords++;
                    continue;
                }

                // Map fields from mitra data to users table; preserve id using sobat_id
                $mapped = [
                    'id' => $itemArray['sobat_id'],
                    'name' => $itemArray['nama_lengkap'] ?? '',
                    'email' => $email,
                    'password' => Hash::make($itemArray['sobat_id']),
                    'profile_photo_path' => $itemArray['foto'] ?? null,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $normalized = SeederUtils::normalizeRow('users', $mapped, true);
                if (!empty($normalized)) {
                    $batchData[] = $normalized;
                }
                
                $processedRecords++;
                
                // Insert batch when we reach batch size
                if (count($batchData) >= $batchSize) {
                    $this->insertBatch($batchData);
                    $insertedRecords += count($batchData);
                    
                    // Show progress
                    if ($processedRecords % (10 * $batchSize) == 0) {
                        echo "Processed $processedRecords records, inserted $insertedRecords records...\n";
                    }
                    
                    // Clear batch data and free memory
                    $batchData = [];
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }
            }
            
            // Insert remaining records
            if (!empty($batchData)) {
                $this->insertBatch($batchData);
                $insertedRecords += count($batchData);
            }
            
            echo "Successfully processed $processedRecords records, inserted $insertedRecords records into users table.\n";
            
        } catch (\Exception $e) {
            echo "Error processing JSON file: " . $e->getMessage() . "\n";
            echo "Processed $processedRecords records before error.\n";
        }
    }
    
    private function insertBatch(array $batchData): void
    {
        try {
            DB::table('users')->insert($batchData);
        } catch (\Exception $e) {
            echo "Error inserting batch: " . $e->getMessage() . "\n";
            // Try inserting one by one as fallback
            foreach ($batchData as $row) {
                try {
                    DB::table('users')->insert($row);
                } catch (\Exception $innerE) {
                    echo "Error inserting individual row: " . $innerE->getMessage() . "\n";
                }
            }
        }
    }
}