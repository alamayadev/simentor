<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Penugasan;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penugasan>
 */
class PenugasanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Penugasan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kegiatan_id' => Kegiatan::factory(),
            'jabatan_tugas' => $this->faker->jobTitle(),
            'mitra_id' => Mitra::factory(),
            'volume' => $this->faker->numberBetween(1, 100),
            'nilai' => $this->faker->numberBetween(100000, 1000000),
            'bln_bayar' => $this->faker->date(),
            'created_by' => User::factory(),
            'no_bast' => $this->faker->numerify('BAST-####'),
            'tgl_bast' => $this->faker->date(),
            'no_sk' => $this->faker->numerify('SK-####'),
            'tgl_sk' => $this->faker->date(),
            'jangka_waktu_mulai' => $this->faker->date(),
            'jangka_waktu_selesai' => $this->faker->date(),
        ];
    }
}
