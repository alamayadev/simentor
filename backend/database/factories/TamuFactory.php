<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tamu;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tamu>
 */
class TamuFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tamu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'no_hp' => $this->faker->phoneNumber(),
            'asal_instansi' => $this->faker->company(),
            'tgl_kunjungan' => $this->faker->date(),
            'tujuan_kunjungan' => $this->faker->sentence(4),
            'jenis_layanan' => $this->faker->word(),
            'detil_layanan' => $this->faker->paragraph(),
        ];
    }
}
