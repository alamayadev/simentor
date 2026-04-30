<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pengaduan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengaduan>
 */
class PengaduanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pengaduan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis_pelangaran' => $this->faker->randomElement(['Korupsi', 'Nepotisme', 'Kekerasan', 'Diskriminasi', 'Pelanggaran Kode Etik']),
            'lainnya' => $this->faker->sentence(5),
            'pelaku' => $this->faker->name(),
            'waktu_kejadian' => $this->faker->date(),
            'kronologi' => $this->faker->paragraph(),
            'bukti' => $this->faker->word() . '.jpg',
        ];
    }
}
