<?php

namespace Database\Factories;

use App\Models\SurtugDetil;
use App\Models\SuratTugas;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurtugDetilFactory extends Factory
{
    protected $model = SurtugDetil::class;

    public function definition()
    {
        $isOrganik = $this->faker->boolean(70); // 70% chance of being organik

        return [
            'surtug_id' => SuratTugas::factory(),
            'pegawai_id' => $isOrganik ? \App\Models\Pegawai::factory() : null,
            'mitra_id' => !$isOrganik ? \App\Models\Mitra::factory() : null,
            'grup_mitra' => !$isOrganik ? $this->faker->numberBetween(1, 5) : null,
            'grup_pegawai' => $isOrganik ? $this->faker->numberBetween(1, 5) : null,
            'penugasan_id' => \App\Models\Penugasan::factory(),
            'dasar' => $this->faker->randomElement([
                'Peraturan BPS No. 1 Tahun 2024',
                'Peraturan Kepala BPS No. 2 Tahun 2024',
                'Undang-Undang No. 16 Tahun 1997',
                'Peraturan Pemerintah No. 51 Tahun 1999',
                'Keputusan Presiden No. 39 Tahun 2019',
            ]),
            'nama_kegiatan' => $this->faker->randomElement([
                'Sensus Penduduk',
                'Sensus Ekonomi',
                'Survei Sosial Ekonomi Nasional',
                'Survei Ketenagakerjaan',
                'Survei Pertanian',
                'Survei Industri',
                'Survei Perdagangan',
                'Survei Konstruksi',
                'Survei Transportasi',
                'Survei Pariwisata',
            ]),
            'tugas_sebagai' => $this->faker->randomElement([
                'Petugas Lapangan',
                'Koordinator Wilayah',
                'Pengawas Lapangan',
                'Supervisor',
                'Editor Data',
                'Pengolah Data',
                'Koordinator Tim',
                'Petugas Pencacah',
            ]),
            'hari' => $this->faker->numberBetween(1, 30),
            'wilayah_kerja' => $this->faker->randomElement([
                'Kecamatan A',
                'Kecamatan B',
                'Kecamatan C',
                'Kecamatan D',
                'Kecamatan E',
                'Kecamatan F',
            ]),
            'tgl_mulai' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'jenis_kendaraan' => $this->faker->randomElement([
                'Motor',
                'Mobil',
                'Sepeda',
                'Tidak ada',
                null,
            ]),
            'no_dipa' => 'DIPA-' . $this->faker->year() . '-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'isOrganik' => $isOrganik,
            'sppd' => $this->faker->boolean(30), // 30% chance of needing SPPD
        ];
    }

    /**
     * Create a SurtugDetil for a specific SuratTugas
     */
    public function forSuratTugas(SuratTugas $suratTugas)
    {
        return $this->state(function (array $attributes) use ($suratTugas) {
            return [
                'surtug_id' => $suratTugas->id,
            ];
        });
    }

    /**
     * Create a SurtugDetil for an organik pegawai
     */
    public function organik()
    {
        return $this->state(function (array $attributes) {
            return [
                'pegawai_id' => \App\Models\Pegawai::factory(),
                'mitra_id' => null,
                'grup_mitra' => null,
                'grup_pegawai' => $this->faker->numberBetween(1, 5),
                'isOrganik' => true,
            ];
        });
    }

    /**
     * Create a SurtugDetil for a mitra
     */
    public function mitra()
    {
        return $this->state(function (array $attributes) {
            return [
                'pegawai_id' => null,
                'mitra_id' => \App\Models\Mitra::factory(),
                'grup_mitra' => $this->faker->numberBetween(1, 5),
                'grup_pegawai' => null,
                'isOrganik' => false,
            ];
        });
    }

    /**
     * Create a SurtugDetil with SPPD
     */
    public function withSppd()
    {
        return $this->state(function (array $attributes) {
            return [
                'sppd' => true,
            ];
        });
    }
}
