<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only truncate tables automatically in non-production environments.
        // Truncation is wrapped in try/catch so it won't break if a table is missing.
    if (env('SEEDER_AUTO_TRUNCATE', false) && app()->environment('local', 'testing')) {
            // Disable FK checks while truncating
            Schema::disableForeignKeyConstraints();

            $tables = [
            'role_has_permissions',
            'model_has_permissions',
            'model_has_roles',
            'permissions',
            'roles',
            'users',
            'profil_pegawai',
            'mitra_kepka',
            'kegiatan',
            'penugasan',
            'settings',
            'skps',
            'surat_tugas',
            'surat_tugas_detil',
            'surat_permintaan',
            'surat_keluar',
            'surat_sk_bast',
            'klasifikasi_surat',
            'pok',
            'uu',
            'surat_sk_detil',
            'links',
            'sls_2024',
            'peta_sls',
            'alokasis',
            'cek_scans',
            'cek_georefs',
            'peta_bs',
            'metas',
        ];

            foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
                // If running via artisan command we can show progress
                if (isset($this->command)) {
                    $this->command->info("Truncated table: {$table}");
                }
            } catch (\Exception $e) {
                if (isset($this->command)) {
                    $this->command->warn("Could not truncate {$table}: " . $e->getMessage());
                }
            }
        }

            Schema::enableForeignKeyConstraints();
        } else {
            if (isset($this->command)) {
                $this->command->warn('Skipping automatic truncation: SEEDER_AUTO_TRUNCATE is not enabled');
            }
        }

        // Now run the seeders
        $this->call([
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            UsersTableSeeder::class,
            ModelHasRolesTableSeeder::class,
            Sls2024TableSeeder::class,
            SlsSipwTableSeeder::class,
            PetaSlsTableSeeder::class,
            ProfilPegawaiTableSeeder::class,
            MitraKepkaTableSeeder::class,
            UsersMitraTableSeeder::class, // Add this line
            KegiatanTableSeeder::class,
            PenugasanTableSeeder::class,
            SettingsTableSeeder::class,
            SkpsTableSeeder::class,
            DataSurtugTableSeeder::class,
            DataSurtugDetilTableSeeder::class,
            DataFormPermintaanTableSeeder::class,
            DataSuratKeluarTableSeeder::class,
            DataSuratSkBastTableSeeder::class,
            KlasifikasiSuratTableSeeder::class,
            PokTableSeeder::class,
            UuTableSeeder::class,
            SkDetilsTableSeeder::class,
            LinksTableSeeder::class,
            AlokasiSeeder::class,
            CekScanSeeder::class,
            CekGeorefSeeder::class,
            SlsKecTableSeeder::class,
            TiketsTableSeeder::class,
            LandmarkTaggingWilkerstatTableSeeder::class,
            DetilConfigurationSeeder::class,
            AssetITSeeder::class,
            MonitoringKegiatanConfigSeeder::class,
            MonitoringKegiatanSeeder::class,
            PetaBsSeeder::class,
            KecamatanSeeder::class,
            DesaSeeder::class,
            MetasTableSeeder::class,
            Sls2025Seeder::class,
        ]);
    }
}
