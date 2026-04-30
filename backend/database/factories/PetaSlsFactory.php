<?php

namespace Database\Factories;

use App\Models\PetaSls;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetaSlsFactory extends Factory
{
    protected $model = PetaSls::class;

    public function definition()
    {
        $kdkec = sprintf('%03d', $this->faker->numberBetween(1, 999));
        $kddesa = sprintf('%03d', $this->faker->numberBetween(1, 999));

        return [
            'kdkec' => $kdkec,
            'kddesa' => $kddesa,
            'nmkec' => $this->faker->citySuffix,
            'nmdesa' => $this->faker->streetName,
            'filename' => sprintf('desa_%s.gpkg', $this->faker->bothify('##########')),
            'operator' => $this->faker->name,
            'jml' => $this->faker->numberBetween(0, 50),
        ];
    }
}
