<?php

namespace Database\Factories;

use App\Models\LaporanPerjalananDinasDokumentasi;
use App\Models\LaporanPerjalananDinas;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanPerjalananDinasDokumentasiFactory extends Factory
{
    protected $model = LaporanPerjalananDinasDokumentasi::class;

    public function definition()
    {
        return [
            'laporan_perjalanan_dinas_id' => LaporanPerjalananDinas::factory(),
            'file_path' => 'uploads/laperdin/example.jpg',
            'deskripsi' => $this->faker->sentence,
            'urutan' => $this->faker->numberBetween(1, 10),
        ];
    }
}
