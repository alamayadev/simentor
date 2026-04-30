<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProfilPegawaiTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                \Illuminate\Support\Facades\DB::table('profil_pegawai')->truncate();
                if (isset($this->command)) $this->command->info('Truncated profil_pegawai');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate profil_pegawai: ' . $e->getMessage());
            }
        }

        // Path to the JSON file
        $jsonFilePath = database_path('initial_data/profil_pegawai.json');

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

        // Get existing user IDs to validate foreign key constraints
        $existingUserIds = DB::table('users')->pluck('id')->toArray();

        echo "Found " . count($existingUserIds) . " existing users in database.\n";

        $rows = [];
        foreach ($data as $row) {
            // Check if the item has a valid id
            if (!isset($row['id']) || empty($row['id'])) {
                echo "Skipping record without valid id\n";
                continue;
            }

            // If user_id is not provided or references a non-existing user,
            // do not skip the profil_pegawai record — instead set user_id to null
            // (the migration already makes this column nullable). Log a warning
            // so we can review records that lost their user link.
            if (!isset($row['user_id']) || !in_array($row['user_id'], $existingUserIds)) {
                $missing = $row['user_id'] ?? 'null';
                if (isset($this->command)) {
                    $this->command->warn("Setting user_id = null for " . ($row['nama'] ?? 'Unknown') . " - user_id {$missing} not found");
                } else {
                    echo "Setting user_id = null for " . ($row['nama'] ?? 'Unknown') . " - user_id {$missing} not found\n";
                }
                $row['user_id'] = null;
            }

            if (!isset($row['created_at'])) $row['created_at'] = now();
            if (!isset($row['updated_at'])) $row['updated_at'] = now();

            // Normalize the row using SeederUtils, preserving the id field
            $normalizedRow = SeederUtils::normalizeRow('profil_pegawai', $row, true);

            $rows[] = $normalizedRow;
        }

        SeederUtils::insertInBatches('profil_pegawai', $rows);

        echo "Seeded " . count($rows) . " records into profil_pegawai table.\n";
    }
}
