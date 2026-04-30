<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CekScan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CekScan>
 */
class CekScanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CekScan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kec' => $this->faker->word,
            'desa' => $this->faker->word,
            'filename' => $this->faker->word . '.JPG',
            'fullpath' => $this->faker->filePath(),
            'created_time' => $this->faker->dateTime(),
            'jenis' => $this->faker->randomElement(['WS', 'WA']),
            'lokasi' => $this->faker->randomElement(['OK', 'NOT OK']),
            'kodename' => $this->faker->word,
            'kode' => $this->faker->numerify('##########'),
        ];
    }
}
