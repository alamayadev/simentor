<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            'Kegiatan','MonitoringKegiatan','KlasifikasiSurat', 'AssetIT', 'AssetITMaintenanceSchedule', 'KlasifikasiSurat', 'BastDetil', 'Link', 'Pengaduan', 'Penugasan', 'Pegawai', 'Mitra', 'Skp', 'SkDetil', 'SkBast', 'Setting', 'SuratTugas', 'SuratPermintaan', 'SuratMasuk', 'SuratKeluar', 'Tiket', 'User', 'Uu', 'UuTambah'
        ];

        $actions = ['View', 'Create', 'Update', 'Delete'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permission = strtolower($model . '-' . $action);
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }
        }
    }
}
