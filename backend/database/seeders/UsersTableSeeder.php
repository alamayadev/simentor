<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Database\Seeders\SeederUtils;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Increase execution time
        ini_set('max_execution_time', 0);

        if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            try {
                SeederUtils::truncateTable('users');
                if (isset($this->command)) $this->command->info('Truncated users');
            } catch (\Exception $e) {
                if (isset($this->command)) $this->command->warn('Could not truncate users: ' . $e->getMessage());
            }
        }

        echo "Starting UsersTableSeeder...\n";

        $json = file_get_contents(database_path('initial_data/users.json'));
        $users = json_decode($json, true);

        $totalUsers = count($users);
        echo "Processing $totalUsers users...\n";

        foreach ($users as $index => $user) {
            // Check if the item has a valid id
            if (!isset($user['id']) || empty($user['id'])) {
                echo "Skipping record without valid id\n";
                continue;
            }

            $normalized = SeederUtils::normalizeRow('users', $user, true);
            // Ensure email is in the match array
            $match = ['email' => $normalized['email'] ?? null];
            if (empty($match['email'])) continue;
            
            // Attributes to update or create with
            $attrs = $normalized;
            // No need to unset password anymore as User model handles re-hashing logic
            User::updateOrCreate($match, $attrs);

            // Show progress every 50 users
            if (($index + 1) % 50 == 0 || ($index + 1) == $totalUsers) {
                echo "Processed " . ($index + 1) . " / $totalUsers users...\n";
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }

        echo "UsersTableSeeder completed.\n";
    }
}
