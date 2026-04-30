<?php

namespace Database\Factories;

use App\Models\SkBast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SkBast>
 */
class SkBastFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SkBast::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggal = $this->faker->dateTimeBetween('-1 year', 'now');
        $carbonDate = Carbon::instance($tanggal);
        $tahun = $carbonDate->format('Y');
        $bulan = $this->convertToRoman($carbonDate->format('m'));
        $nomor = str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'thn' => $tahun,
            'bln' => $bulan,
            'tanggal' => $carbonDate->format('Y-m-d'),
            'nomor' => $nomor,
            'no_sisip' => null,
            'kode_klas' => $this->faker->optional(0.3)->randomElement(['100', '200', '300', '400', '500']),
            'no_surat' => $nomor . '/SK/' . $bulan . '/' . $tahun,
            'oleh' => $this->faker->randomElement([
                'Kepala BPS Kota ' . $this->faker->city,
                'Kepala BPS Kabupaten ' . $this->faker->city,
                'Kepala Bagian Tata Usaha',
                'Sekretaris BPS',
                'Koordinator Statistik Kecamatan'
            ]),
            'kegiatan' => $this->faker->randomElement([
                'Survei Sosial Ekonomi Nasional (SUSENAS)',
                'Sensus Penduduk',
                'Survei Angkatan Kerja Nasional (SAKERNAS)',
                'Pendataan Potensi Desa (PODES)',
                'Survei Struktur Ongkos Usaha Industri Pengolahan',
                'Sensus Ekonomi',
                'Survei Konsumsi Rumah Tangga',
                'Pendataan Usaha/Perusahaan Statistik Dasar',
                'Survei Penduduk Antar Sensus (SUPAS)',
                'Survei Pertanian Antar Sensus (SUTAS)'
            ]),
            'kepada' => $this->faker->randomElement([
                'Tim Koordinator Statistik Kecamatan',
                'Petugas Pencacah Lapangan',
                'Pengawas Mitra Statistik',
                'Tim Pengolah Data',
                'Koordinator Wilayah Survei',
                'Petugas Entri Data',
                'Tim Monitoring dan Evaluasi',
                'Supervisor Lapangan',
                'Ketua Tim Survei',
                'Koordinator Logistik'
            ]),
            'perihal' => $this->faker->randomElement([
                'Penunjukan Tim Koordinator Statistik Kecamatan',
                'Penunjukan Petugas Pencacah Lapangan',
                'Penunjukan Pengawas Mitra Statistik',
                'Penunjukan Tim Pengolah Data',
                'Penunjukan Koordinator Wilayah Survei',
                'Penunjukan Petugas Entri Data',
                'Penunjukan Tim Monitoring dan Evaluasi',
                'Penunjukan Supervisor Lapangan'
            ]),
            'type' => 'SK',
            'kol_lampiran' => $this->faker->optional(0.6)->randomElement([
                'Surat Tugas',
                'Daftar Nama Petugas',
                'Surat Tugas;Daftar Nama Petugas',
                'Daftar Wilayah Kerja',
                'Petunjuk Teknis;Daftar Nama',
                'SK Pembentukan Tim;Uraian Tugas'
            ]),
            'create_by' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Indicate that the surat keputusan has a sisip number.
     */
    public function withSisip(): static
    {
        return $this->state(fn (array $attributes) => [
            'no_sisip' => $this->faker->numberBetween(1, 9),
            'no_surat' => $attributes['nomor'] . '.' . $this->faker->numberBetween(1, 9) . '/SK/' . $attributes['bln'] . '/' . $attributes['thn']
        ]);
    }

    /**
     * Indicate that the surat keputusan is for a specific year.
     */
    public function forYear(string $year): static
    {
        return $this->state(fn (array $attributes) => [
            'thn' => $year,
            'tanggal' => $this->faker->dateTimeBetween($year . '-01-01', $year . '-12-31')->format('Y-m-d'),
            'bln' => $this->convertToRoman($this->faker->numberBetween(1, 12)),
            'no_surat' => $attributes['nomor'] . '/SK/' . $this->convertToRoman($this->faker->numberBetween(1, 12)) . '/' . $year
        ]);
    }

    /**
     * Indicate that the surat keputusan is for a specific month.
     */
    public function forMonth(string $year, string $month): static
    {
        $paddedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
        $romanMonth = $this->convertToRoman($month);
        $startDate = $year . '-' . $paddedMonth . '-01';
        $endDate = $year . '-' . $paddedMonth . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return $this->state(fn (array $attributes) => [
            'thn' => $year,
            'bln' => $romanMonth,
            'tanggal' => $this->faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'),
            'no_surat' => $attributes['nomor'] . '/SK/' . $romanMonth . '/' . $year
        ]);
    }

    /**
     * Convert number to Roman numeral
     */
    private function convertToRoman($number)
    {
        $mapping = [
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I'
        ];

        $result = '';
        foreach ($mapping as $value => $roman) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }

        return $result;
    }
}
