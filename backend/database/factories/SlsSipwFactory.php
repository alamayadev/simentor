<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SlsSipw;

class SlsSipwFactory extends Factory
{
    protected $model = SlsSipw::class;

    public function definition()
    {
        return [
            'idfrs' => $this->faker->unique()->numerify('########'),
            'idsls' => $this->faker->unique()->numerify('###########'),
            'kdprov' => $this->faker->numerify('##'),
            'kdkab' => $this->faker->numerify('##'),
            'kdkec' => $this->faker->numerify('###'),
            'kddesa' => $this->faker->numerify('###'),
            'kdsls' => $this->faker->numerify('####'),
            'klas' => $this->faker->numberBetween(1, 5),
            'nmprov' => $this->faker->state,
            'nmkab' => $this->faker->city,
            'nmkec' => $this->faker->citySuffix,
            'nmdesa' => $this->faker->streetName,
            'nama_sls' => $this->faker->sentence(3),
            'jenis_sls' => $this->faker->randomElement(['SLS', 'NON_SLS']),
            'ketua_sls' => $this->faker->name,
            'j_subsls' => $this->faker->numberBetween(0, 50),
            'muatan_dominan' => (string) $this->faker->numberBetween(0, 5),
            'flag_perubahan_sls' => $this->faker->numberBetween(0, 1),
            'status_olah_peta' => $this->faker->randomElement(['SUDAH', 'PROSES', 'BELUM']),
            'shapes_comparation' => (string) $this->faker->randomFloat(2, 0, 100),
            'location' => $this->faker->word,
            'status_sls' => $this->faker->optional()->word,
            'peta_banding_rs' => $this->faker->optional()->randomElement(['MATCH', 'TIDAK_MATCH']),
            'operator' => $this->faker->name,
        ];
    }
}
