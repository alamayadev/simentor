<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Alokasi;

class AlokasiFactory extends Factory
{
    protected $model = Alokasi::class;

    public function definition()
    {
        return [
            'idsls' => 'S' . $this->faker->unique()->numerify('#####'),
            'kdprov' => $this->faker->numerify('##'),
            'kdkab' => $this->faker->numerify('##'),
            'kdkec' => $this->faker->numerify('###'),
            'kddesa' => $this->faker->numerify('###'),
            'kdsls' => $this->faker->numerify('####'),
            'nmprov' => $this->faker->state,
            'nmkab' => $this->faker->city,
            'nmkec' => $this->faker->city,
            'nmdesa' => $this->faker->city,
            'nmsls' => $this->faker->streetName,
            'periode' => $this->faker->year . '_1',
            'idsubsls' => $this->faker->numerify('##################'),
            'iddesa' => $this->faker->numerify('##########'),
            'alokasi_scan' => null,
            'alokasi_georef' => null,
            'alokasi_geojson' => json_encode(['operator' => $this->faker->company]),
            'alokasi_muatan' => null,
        ];
    }
}
