<?php

namespace Database\Factories;

use App\Models\LaporanPerjalananDinas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanPerjalananDinasFactory extends Factory
{
    protected $model = LaporanPerjalananDinas::class;

    public function definition()
    {
        return [
            'nama_traveler' => $this->faker->name,
            'tujuan' => $this->faker->city,
            'lama_tanggal' => '2 Hari (15-16 Januari 2024)',
            'dalam_rangka' => $this->faker->sentence,
            'pembebanan' => 'DIPA ' . date('Y'),
            'user_id' => User::factory(),
            'status' => 'draft',
        ];
    }
}
