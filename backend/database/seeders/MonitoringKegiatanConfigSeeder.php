<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MonitoringKegiatanConfig;
use App\Models\DetilConfiguration;

class MonitoringKegiatanConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample monitoring configuration templates
        $configs = [
            [
                'fungsi' => 'Fungsi Statistik Sosial',
                'kegiatan_id' => '01',
                'detil_configurations' => []
            ],
            [
                'fungsi' => 'Fungsi Statistik Produksi',
                'kegiatan_id' => '02',
                'detil_configurations' => []
            ],
            [
                'fungsi' => 'Fungsi Statistik Distribusi',
                'kegiatan_id' => '03',
                'detil_configurations' => []
            ],
        ];

        foreach ($configs as $configData) {
            // First, let's get some sample detil configurations if they exist
            $sampleConfigs = DetilConfiguration::limit(3)->pluck('id')->toArray();

            $configData['detil_configurations'] = $sampleConfigs;

            MonitoringKegiatanConfig::updateOrCreate(
                [
                    'fungsi' => $configData['fungsi'],
                    'kegiatan_id' => $configData['kegiatan_id'],
                ],
                [
                    'detil_configurations' => $configData['detil_configurations'],
                ]
            );
        }
    }
}
