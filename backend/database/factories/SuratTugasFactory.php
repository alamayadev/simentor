<?php

namespace Database\Factories;

use App\Models\SuratTugas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SuratTugasFactory extends Factory
{
    protected $model = SuratTugas::class;

    public function definition()
    {
        $tanggal = $this->faker->dateTimeBetween('-1 year', 'now');
        $tahun = $tanggal->format('Y');
        $nomor = $this->faker->numberBetween(1, 9999);
        $kode_klas = $this->faker->randomElement(['100', '200', '300', '400', '500']);

        // Generate no_mix and no_surat
        $nomorFormatted = str_pad($nomor, 4, '0', STR_PAD_LEFT);
        $no_surat = $nomorFormatted . '/ST-' . $kode_klas . '/' . $tahun;

        return [
            'tahun' => $tahun,
            'tanggal' => $tanggal->format('Y-m-d'),
            'nomor' => $nomorFormatted,
            'no_sisip' => null,
            'no_mix' => $nomorFormatted,
            'no_surat' => $no_surat,
            'tanggal_indo' => Carbon::parse($tanggal)
                                   ->locale('id')
                                   ->settings(['formatFunction' => 'translatedFormat'])
                                   ->format('j F Y'),
            'kode_klas' => $kode_klas,
            'kepada' => $this->faker->randomElement([
                'Tim Koordinator Survei',
                'Tim Pengumpul Data',
                'Petugas Lapangan',
                'Koordinator Wilayah',
                'Tim Supervisi',
                'Penanggung Jawab Kegiatan',
                'Tim Pengolah Data',
                'Koordinator Administrasi'
            ]),
            'menimbang' => $this->faker->randomElement([
                'Dalam rangka pelaksanaan kegiatan survei',
                'Untuk melaksanakan tugas pengumpulan data',
                'Dalam rangka kegiatan monitoring',
                'Untuk melaksanakan supervisi lapangan',
                'Dalam rangka pelaksanaan program kerja',
                'Untuk melaksanakan koordinasi kegiatan'
            ]),
            'uraian' => $this->faker->randomElement([
                'Melaksanakan survei sosial ekonomi nasional',
                'Mengumpulkan data statistik di wilayah kerja',
                'Melakukan supervisi kegiatan pengumpulan data',
                'Melaksanakan monitoring pelaksanaan survei',
                'Mengkoordinasikan kegiatan tim lapangan',
                'Melakukan verifikasi data hasil survei',
                'Melaksanakan pelatihan petugas lapangan',
                'Mengawasi pelaksanaan kegiatan statistik'
            ]),
            'file' => null,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a SuratTugas with sisip number
     */
    public function withSisip()
    {
        return $this->state(function (array $attributes) {
            $no_sisip = $this->faker->numberBetween(1, 9);
            $no_mix = $attributes['nomor'] . '.' . $no_sisip;
            $no_surat = str_replace($attributes['nomor'], $no_mix, $attributes['no_surat']);

            return [
                'no_sisip' => (string) $no_sisip,
                'no_mix' => $no_mix,
                'no_surat' => $no_surat,
            ];
        });
    }

    /**
     * Create a SuratTugas for specific year
     */
    public function forYear($year)
    {
        return $this->state(function (array $attributes) use ($year) {
            $tanggal = $this->faker->dateTimeBetween($year . '-01-01', $year . '-12-31');
            $nomor = str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
            $kode_klas = $attributes['kode_klas'] ?? $this->faker->randomElement(['100', '200', '300']);
            $no_surat = $nomor . '/ST-' . $kode_klas . '/' . $year;

            return [
                'tahun' => (string) $year,
                'tanggal' => $tanggal->format('Y-m-d'),
                'nomor' => $nomor,
                'no_mix' => $nomor,
                'no_surat' => $no_surat,
                'tanggal_indo' => Carbon::parse($tanggal)
                                       ->locale('id')
                                       ->settings(['formatFunction' => 'translatedFormat'])
                                       ->format('j F Y'),
            ];
        });
    }

    /**
     * Create a SuratTugas with specific klasifikasi
     */
    public function withKlasifikasi($kode_klas)
    {
        return $this->state(function (array $attributes) use ($kode_klas) {
            $nomor = $attributes['nomor'];
            $tahun = $attributes['tahun'];
            $no_surat = $nomor . '/ST-' . $kode_klas . '/' . $tahun;

            return [
                'kode_klas' => $kode_klas,
                'no_surat' => $no_surat,
            ];
        });
    }
}
