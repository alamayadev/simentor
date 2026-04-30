<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $jsonFilePath = database_path('initial_data/roles.json');

        if (!\Illuminate\Support\Facades\File::exists($jsonFilePath)) {
            // fallback to default list
            $roles = [
                'super-admin',
                'admin',
                'organik',
                'mitra',
                'user',
            ];
            foreach ($roles as $role) {
                Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            }
            return;
        }

        $json = \Illuminate\Support\Facades\File::get($jsonFilePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Invalid JSON in file: $jsonFilePath\n";
            return;
        }

        foreach ($data as $row) {
            $name = $row['name'] ?? null;
            $guard = $row['guard_name'] ?? 'web';
            if ($name) {
                Role::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }
    }
}
