<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CekGeoref;

class CekGeorefFactory extends Factory
{
    protected $model = CekGeoref::class;

    public function definition(): array
    {
        return [
            'kec' => $this->faker->word,
            'desa' => $this->faker->word,
            'level_3' => $this->faker->optional()->word,
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
