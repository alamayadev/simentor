<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MonitoringKegiatanConfig;

class MonitoringKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, create monitoring configuration templates
        $config1 = MonitoringKegiatanConfig::firstOrCreate(
            ['fungsi' => 'Sosial', 'kegiatan_id' => 88],
            ['detil_configurations' => [1]]
        );

        $config2 = MonitoringKegiatanConfig::firstOrCreate(
            ['fungsi' => 'Produksi', 'kegiatan_id' => 68],
            ['detil_configurations' => [2]]
        );

        $config3 = MonitoringKegiatanConfig::firstOrCreate(
            ['fungsi' => 'Distribusi', 'kegiatan_id' => 33],
            ['detil_configurations' => [3]]
        );

        $config4 = MonitoringKegiatanConfig::firstOrCreate(
            ['fungsi' => 'Nerwilis', 'kegiatan_id' => 12],
            ['detil_configurations' => [1, 2]]
        );

        $monitoringKegiatan = [
            [
                'fungsi' => 'Sosial',
                'kegiatan_id' => 88,
                'kec_id' => '3215112',
                'desa_id' => '3215112001',
                'kode_sampel' => 'SMP-001',
                'monitoring_kegiatan_config_id' => $config1->id,
                'detil_data' => json_encode([
                    'mitra' => 'Santosa'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fungsi' => 'Produksi',
                'kegiatan_id' => 68,
                'kec_id' => '3215112',
                'desa_id' => '3215112001',
                'kode_sampel' => 'SMP-002',
                'monitoring_kegiatan_config_id' => $config2->id,
                'detil_data' => json_encode([
                    'status_pelaksanaan' => 'sedang berjalan'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fungsi' => 'Distribusi',
                'kegiatan_id' => 33,
                'kec_id' => '3215113',
                'desa_id' => '3215113008',
                'kode_sampel' => 'SMP-003',
                'monitoring_kegiatan_config_id' => $config3->id,
                'detil_data' => json_encode([
                    'tanggal_survey' => '2025-12-20'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fungsi' => 'Nerwilis',
                'kegiatan_id' => 12,
                'kec_id' => '3215090',
                'desa_id' => '3215090001',
                'kode_sampel' => 'SMP-004',
                'monitoring_kegiatan_config_id' => $config4->id,
                'detil_data' => json_encode([
                    'mitra' => 'Amrini',
                    'status_pelaksanaan' => 'selesai'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('monitoring_kegiatan')->insert($monitoringKegiatan);
    }
}
