<?php

namespace Database\Factories;

use App\Models\RawData;
use Illuminate\Database\Eloquent\Factories\Factory;

class RawDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RawData::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'fungsi' => $this->faker->randomElement(['IPDS', 'Sosial', 'Produksi', 'Distribusi', 'Nerwilis']),
            'nama' => $this->faker->sentence(),
            'keterangan' => $this->faker->paragraph(),
            'file' => 'raw_data/' . $this->faker->word() . '.zip',
        ];
    }
}
