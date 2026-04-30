<?php

namespace Database\Factories;

use App\Models\Skp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkpFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Skp::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'jenis' => $this->faker->randomElement(['SKP Bulanan', 'SKP Tahunan (Penetapan)', 'SKP Tahunan (Penilaian)']),
            'nama' => $this->faker->sentence,
            'bulan' => $this->faker->optional()->numberBetween(1, 12),
            'tahun' => $this->faker->year,
            'link' => $this->faker->url,
            'konten' => $this->faker->optional()->text,
        ];
    }
}
