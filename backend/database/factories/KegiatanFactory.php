<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kegiatan;
use App\Enums\FungsiType;
use App\Enums\JenisKegiatanType;
use App\Enums\SatuanType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kegiatan>
 */
class KegiatanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Kegiatan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tahun' => $this->faker->year(),
            'fungsi' => $this->faker->randomElement([
                FungsiType::UMUM->value,
                FungsiType::DISTRIBUSI->value,
                FungsiType::PRODUKSI->value,
                FungsiType::SOSIAL->value,
                FungsiType::NERWILIS->value,
                FungsiType::IPDS->value,
            ]),
            'kode_kelompok_kegiatan' => $this->faker->randomNumber(2),
            'kode_kegiatan' => $this->faker->randomNumber(2) . '.' . $this->faker->randomNumber(2),
            'nama' => $this->faker->sentence(3),
            'tgl_mulai' => $this->faker->date(),
            'tgl_selesai' => $this->faker->date(),
            'jenis_kegiatan' => $this->faker->randomElement([
                JenisKegiatanType::PERSIAPAN->value,
                JenisKegiatanType::PENGUMPULAN_DATA->value,
                JenisKegiatanType::PENGOLAHAN->value,
                JenisKegiatanType::DISEMINASI->value,
                JenisKegiatanType::SUPERVISI->value,
            ]),
            'jml_ptgs' => $this->faker->numberBetween(10, 100),
            'volume' => $this->faker->numberBetween(100, 1000),
            'satuan' => $this->faker->randomElement([
                SatuanType::Resp->value,
                SatuanType::Dok->value,
                SatuanType::Set->value,
                SatuanType::BUAH->value,
            ]),
            'rate_pcl' => $this->faker->numberBetween(50000, 100000),
            'rate_pml' => $this->faker->numberBetween(75000, 150000),
            'rate_entri' => $this->faker->numberBetween(10000, 50000),
        ];
    }
}
