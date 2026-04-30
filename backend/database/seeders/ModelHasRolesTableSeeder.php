<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $json = file_get_contents(database_path('initial_data/model_has_roles.json'));
        $modelHasRoles = json_decode($json, true);

        // Prepare rows and perform upserts in reasonable-sized chunks
        $rows = [];
        foreach ($modelHasRoles as $modelHasRole) {
            $rows[] = SeederUtils::normalizeRow('model_has_roles', $modelHasRole);
        }

        // Perform upserts in batches to avoid extremely large single transactions
        $chunks = array_chunk($rows, 500);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $r) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $r['role_id'] ?? null,
                        'model_type' => $r['model_type'] ?? null,
                        'model_id' => $r['model_id'] ?? null,
                    ],
                    $r
                );
            }
        }
    }
}
