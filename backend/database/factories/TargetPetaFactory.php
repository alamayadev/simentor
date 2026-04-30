<?php

namespace Database\Factories;

use App\Models\TargetPeta;
use Illuminate\Database\Eloquent\Factories\Factory;

class TargetPetaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TargetPeta::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'filename' => $this->faker->unique()->word . '.geojson',
            'keterangan' => $this->faker->optional()->sentence(),
        ];
    }
}
