<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('initial_data/metas.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error("File not found: {$jsonPath}");
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $metas = json_decode($jsonContent, true);

        if (empty($metas)) {
            $this->command->error("No data found in metas.json");
            return;
        }

        // Disable foreign key checks for seeding
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        
        \Illuminate\Support\Facades\DB::table('metas')->truncate();

        foreach ($metas as $meta) {
            \App\Models\Meta::create([
                'id' => $meta['id'],
                'parent_id' => $meta['parent_id'],
                'name' => $meta['name'],
                'name2' => $meta['name2'] ?? null,
            ]);
        }

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        
        $this->command->info("Seeded " . count($metas) . " entries into metas table.");
    }
}
