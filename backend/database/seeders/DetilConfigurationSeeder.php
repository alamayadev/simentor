<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetilConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            [
                'name' => 'Petugas',
                'related_table' => 'penugasan',
                'foreign_key' => 'kegiatan_id',
                'field' => json_encode([
                    'name' => 'mitra',
                    'label' => 'Petugas',
                    'source' => 'database',
                    'required' => true
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Status Pelaksanaan',
                'related_table' => null,
                'foreign_key' => null,
                'field' => json_encode([
                    'name' => 'status_pelaksanaan',
                    'label' => 'Status Pelaksanaan',
                    'type' => 'enum',
                    'source' => 'custom',
                    'options' => ['belum_dimulai', 'sedang_berjalan', 'selesai', 'ditunda'],
                    'required' => true
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Tanggal Survey',
                'related_table' => null,
                'foreign_key' => null,
                'field' => json_encode([
                    'name' => 'tanggal_survey',
                    'label' => 'Tanggal Survey',
                    'type' => 'date',
                    'source' => 'custom',
                    'required' => true
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Blok Sensus',
                'related_table' => 'sls2025',
                'foreign_key' => 'desa_id',
                'field' => json_encode([
                    'name' => 'blok_sensus',
                    'label' => 'Blok Sensus',
                    'source' => 'database',
                    'required' => true
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'SLS',
                'related_table' => 'sls2025',
                'foreign_key' => 'desa_id',
                'field' => json_encode([
                    'name' => 'sls',
                    'label' => 'SLS',
                    'source' => 'database',
                    'required' => false
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Pengawas',
                'related_table' => 'penugasan',
                'foreign_key' => 'kegiatan_id',
                'field' => json_encode([
                    'name' => 'pml',
                    'label' => 'Pengawas',
                    'source' => 'database',
                    'required' => true
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Supervisor',
                'related_table' => 'supervisor',
                'foreign_key' => 'kegiatan_id',
                'field' => json_encode([
                    'name' => 'supervisor',
                    'label' => 'Supervisor',
                    'source' => 'database',
                    'required' => true
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('detil_configurations')->insert($configurations);
    }
}
