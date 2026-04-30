<?php

namespace Database\Factories;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PegawaiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pegawai::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama' => $this->faker->name,
            'pangkat' => $this->faker->randomElement(['Pengatur', 'Penata', 'Pembina']),
            'gol' => $this->faker->randomElement(['II/c', 'III/a', 'III/c', 'IV/a']),
            'nip' => $this->faker->unique()->numerify('################'),
            'gelar_depan' => $this->faker->optional()->randomElement(['Dr.', 'Ir.', 'Prof.']),
            'gelar_belakang' => $this->faker->optional()->randomElement(['S.Kom', 'M.Kom', 'S.Si']),
            'tempat_lahir' => $this->faker->optional()->city,
            'tanggal_lahir' => $this->faker->optional()->date(),
            'alamat' => $this->faker->optional()->address,
            'no_hp' => $this->faker->optional()->numerify('08##########'),
            'jabatan' => $this->faker->randomElement(['Kepala Seksi', 'Staf', 'Analis']),
            'kelas' => $this->faker->randomElement(['I', 'II', 'III', 'IV']),
            'user_id' => User::factory(),
            'status' => $this->faker->optional()->randomElement(['aktif', 'cuti', 'pensiun']),
        ];
    }
}
