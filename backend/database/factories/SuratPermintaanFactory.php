<?php

namespace Database\Factories;

use App\Models\SuratPermintaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuratPermintaanFactory extends Factory
{
    protected $model = SuratPermintaan::class;

    public function definition(): array
    {
        $tahun = $this->faker->numberBetween(2020, 2024);
        $tanggal = $this->faker->date('Y-m-d');
        $nomor = str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'thn' => $tahun,
            'bulan' => $this->faker->numberBetween(1, 12),
            'tanggal' => $tanggal,
            'nomor' => $nomor,
            'no_sisip' => null,
            'tanggal_indo' => $this->faker->date('j F Y'),
            'kode_klas' => $this->faker->randomElement(['100', '200', '300']),
            'no_surat' => $nomor . '/BPS-100/' . $tahun,
            'dari' => $this->faker->company(),
            'perihal' => $this->faker->sentence(6),
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withSisip(): static
    {
        return $this->state(function (array $attributes) {
            $no_sisip = $this->faker->numberBetween(1, 10);
            $no_surat = $attributes['nomor'] . '.' . $no_sisip . '/BPS-100/' . $attributes['thn'];

            return [
                'no_sisip' => $no_sisip,
                'no_surat' => $no_surat,
            ];
        });
    }
}
