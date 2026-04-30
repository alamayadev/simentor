<?php

namespace Database\Factories;

use App\Models\SuratKeluar;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuratKeluar>
 */
class SuratKeluarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SuratKeluar::class;

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
        $bulan = $carbonDate->format('m');
        $nomor = str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'thn' => $tahun,
            'bln' => $bulan,
            'tanggal' => $carbonDate->format('Y-m-d'),
            'nomor' => $nomor,
            'no_sisip' => null,
            'tanggal_indo' => $carbonDate->locale('id')->translatedFormat('j F Y'),
            'no_surat' => $nomor . '/SK/' . $tahun,
            'dari' => $this->faker->randomElement([
                'Kepala BPS Kota ' . $this->faker->city,
                'Kepala BPS Kabupaten ' . $this->faker->city,
                'Kepala Bagian Tata Usaha',
                'Kepala Seksi Statistik Sosial',
                'Kepala Seksi Statistik Produksi',
                'Kepala Seksi Statistik Distribusi',
                'Kepala Seksi Neraca Wilayah dan Analisis Statistik',
                'Kepala Seksi Integrasi Pengolahan dan Diseminasi Statistik'
            ]),
            'tujuan' => $this->faker->randomElement([
                'Dinas Pendidikan Kota ' . $this->faker->city,
                'Dinas Kesehatan Kota ' . $this->faker->city,
                'Dinas Sosial Kota ' . $this->faker->city,
                'Dinas Pekerjaan Umum Kota ' . $this->faker->city,
                'Dinas Tenaga Kerja Kota ' . $this->faker->city,
                'Badan Perencanaan Pembangunan Daerah',
                'Sekretariat Daerah Kota ' . $this->faker->city,
                'Camat ' . $this->faker->city,
                'Lurah ' . $this->faker->streetName,
                'PT. ' . $this->faker->company
            ]),
            'perihal' => $this->faker->randomElement([
                'Pemberitahuan Pelaksanaan Survei Sosial Ekonomi Nasional (SUSENAS)',
                'Undangan Rapat Koordinasi Kegiatan Statistik',
                'Permohonan Data Statistik Sektoral',
                'Pemberitahuan Pelaksanaan Sensus Ekonomi',
                'Undangan Sosialisasi Metodologi Survei',
                'Permohonan Kerjasama Penyediaan Data',
                'Pemberitahuan Kegiatan Pendataan Statistik',
                'Undangan Workshop Analisis Data Statistik',
                'Permohonan Bantuan Koordinasi Lapangan',
                'Pemberitahuan Jadwal Pelaksanaan Survei Angkatan Kerja Nasional (SAKERNAS)',
                'Undangan Pembahasan Indikator Statistik Daerah',
                'Permohonan Validasi Data Statistik Sektoral'
            ]),
            'isi_surat' => $this->faker->paragraph(3),
            'lampiran' => $this->faker->optional(0.7)->numberBetween(0, 5),
            'file' => $this->faker->optional(0.3)->word . '.pdf',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Indicate that the surat keluar has a sisip number.
     */
    public function withSisip(): static
    {
        return $this->state(fn (array $attributes) => [
            'no_sisip' => $this->faker->numberBetween(1, 9),
            'no_surat' => $attributes['nomor'] . '.' . $this->faker->numberBetween(1, 9) . '/SK/' . $attributes['thn']
        ]);
    }

    /**
     * Indicate that the surat keluar is for a specific year.
     */
    public function forYear(string $year): static
    {
        return $this->state(fn (array $attributes) => [
            'thn' => $year,
            'tanggal' => $this->faker->dateTimeBetween($year . '-01-01', $year . '-12-31')->format('Y-m-d'),
            'bln' => str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT),
            'no_surat' => $attributes['nomor'] . '/SK/' . $year
        ]);
    }

    /**
     * Indicate that the surat keluar is for a specific month.
     */
    public function forMonth(string $year, string $month): static
    {
        $paddedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
        $startDate = $year . '-' . $paddedMonth . '-01';
        $endDate = $year . '-' . $paddedMonth . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return $this->state(fn (array $attributes) => [
            'thn' => $year,
            'bln' => $paddedMonth,
            'tanggal' => $this->faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'),
            'no_surat' => $attributes['nomor'] . '/SK/' . $year
        ]);
    }
}
