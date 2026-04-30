<?php

namespace Database\Factories;

use App\Models\LaporanPerjalananDinasDetail;
use App\Models\LaporanPerjalananDinas;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanPerjalananDinasDetailFactory extends Factory
{
    protected $model = LaporanPerjalananDinasDetail::class;

    public function definition()
    {
        return [
            'laporan_perjalanan_dinas_id' => LaporanPerjalananDinas::factory(),
            'tanggal' => $this->faker->date(),
            'uraian_lhp' => $this->faker->paragraph,
            'kendala' => $this->faker->sentence,
            'solusi' => $this->faker->sentence,
        ];
    }
}
